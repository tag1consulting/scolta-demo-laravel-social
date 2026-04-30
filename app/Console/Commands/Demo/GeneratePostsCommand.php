<?php

namespace App\Console\Commands\Demo;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeneratePostsCommand extends Command
{
    protected $signature = 'demo:generate-posts {--fresh : Drop all existing posts first} {--user= : Only generate for a specific username}';
    protected $description = 'Generate 5,000–8,000 posts across all users';

    private string $apiKey;
    private string $model = 'claude-haiku-4-5-20251001';

    private array $postCountByFrequency = [
        'power'    => [180, 320],
        'moderate' => [60, 140],
        'casual'   => [8, 28],
    ];

    public function handle(): int
    {
        $this->apiKey = env('SCOLTA_API_KEY', env('ANTHROPIC_API_KEY', ''));
        if (! $this->apiKey) {
            $this->error('SCOLTA_API_KEY or ANTHROPIC_API_KEY must be set.');
            return 1;
        }

        if ($this->option('fresh')) {
            Post::query()->delete();
            Hashtag::query()->delete();
            $this->line('Cleared existing posts and hashtags.');
        }

        $users = User::all();
        if ($users->isEmpty()) {
            $this->error('No users found. Run demo:generate-users first.');
            return 1;
        }

        if ($this->option('user')) {
            $users = $users->where('username', $this->option('user'));
        }

        $this->info("Generating posts for {$users->count()} users…");

        $now = Carbon::create(2026, 4, 30, 23, 59, 59);

        foreach ($users as $user) {
            if (Post::where('user_id', $user->id)->whereNull('parent_id')->count() > 0) {
                $this->line("  Skipping {$user->username} (already has posts)");
                continue;
            }

            $targetCount = rand(...$this->postCountByFrequency[$user->posting_frequency]);
            $joinedAt = Carbon::parse($user->joined_at);
            $activeDays = $joinedAt->diffInDays($now);

            $this->line("  Generating {$targetCount} posts for @{$user->username} ({$user->archetype})…");

            $batchSize = 30;
            $batches = (int) ceil($targetCount / $batchSize);
            $allPosts = [];

            for ($b = 0; $b < $batches; $b++) {
                $count = ($b === $batches - 1) ? ($targetCount - count($allPosts)) : $batchSize;
                if ($count <= 0) {
                    break;
                }

                // Determine approximate time window for this batch
                $batchFraction = $b / $batches;
                $daysIntoActive = (int) ($batchFraction * $activeDays);
                $batchDate = $joinedAt->copy()->addDays($daysIntoActive);
                $timePeriod = $this->describeTimePeriod($batchDate);

                $prompt = $this->buildPostPrompt($user, $count, $timePeriod, $allPosts);
                $posts = $this->callAi($prompt, 3000);

                if (! $posts) {
                    $this->warn("    Batch {$b} failed for @{$user->username}, skipping.");
                    continue;
                }

                $allPosts = array_merge($allPosts, $posts);
                usleep(200000); // 200ms between calls
            }

            // Insert posts with distributed timestamps
            $this->insertPosts($user, $allPosts, $joinedAt, $now);
        }

        $this->info('');
        $this->info("✓ Post generation complete. Total posts: " . Post::whereNull('parent_id')->count());

        // Now generate reply threads
        $this->generateReplies($now);

        $this->info("✓ Total posts including replies: " . Post::count());
        return 0;
    }

    private function buildPostPrompt(User $user, int $count, string $timePeriod, array $existing): string
    {
        $archetypeHints = $this->archetypeHints($user->archetype);
        $secondaryHints = $this->archetypeHints($user->secondary_interest ?? 'commuter');
        $existingSample = '';

        if (count($existing) >= 3) {
            $sample = array_slice($existing, -3);
            $existingSample = "\nRecent posts from this person (for voice consistency):\n- " . implode("\n- ", $sample);
        }

        return <<<PROMPT
Write exactly {$count} social media posts for this person on MyStream.

Person: {$user->display_name} (@{$user->username})
Bio: {$user->bio}
Main interest: {$user->archetype} — {$archetypeHints}
Secondary interest: {$user->secondary_interest} — {$secondaryHints}
Time period: {$timePeriod}
{$existingSample}

Rules:
- Return ONLY a valid JSON array of {$count} strings. No markdown, no explanation.
- Each string is one post (20–280 characters)
- Casual, first-person, conversational — like texting or talking to yourself
- VARY length: some are 1-line thoughts, some are 2-3 sentences
- Reference mundane real life: their specific pets, meals, commute annoyances, wins, weather
- Use hashtags in ~20% of posts, max 2 per post (e.g. #mondaymotivation #dogsofmystream #gardenlife)
- Use emojis naturally in ~30% of posts — not on every post, not forced
- NO politics, NO hate speech, NO real brand names (say "the coffee shop" not "Starbucks"), NO dark topics
- Reference this time period naturally: {$timePeriod} (seasons, weather, what's happening)
- Vary the MOOD: happy, mildly frustrated, amused, curious, satisfied, tired, excited
- Some posts are totally off-topic from their main interest — just life
- Make them feel like one specific real human, not a generic content creator

Return ONLY the JSON array of {$count} strings.
PROMPT;
    }

    private function archetypeHints(string $archetype): string
    {
        $hints = [
            'pet_parent'    => 'dogs/cats destroying things, vet visits, funny pet moments, training fails/wins, pet photos described in text',
            'home_cook'     => 'recipe experiments, farmers market finds, cooking disasters, "made this from scratch", restaurant comparisons, grocery store observations',
            'fitness'       => 'runs, gym sessions, step counts, rest days, soreness, new PRs, workout gear, progress updates, healthy food vs. giving in to cravings',
            'new_parent'    => 'sleep deprivation, toddler/baby milestones, daycare chaos, "the kid said the funniest thing", identity shifts, other parents solidarity',
            'gardener'      => 'seasonal planting, pest drama, harvest excitement, seedlings, soil amendments, "something is eating my X", what finally grew',
            'commuter'      => 'traffic nightmares, public transit observations, podcast recommendations, audiobook progress, road rage (mild), weather impact on commute',
            'remote_worker' => 'WFH bliss vs. chaos, cat on keyboard, video call mishaps, coffee consumption, home office setup, missing/not missing coworkers, boundaries between work and home',
            'student'       => 'exam stress, study sessions, group project chaos, campus food, job hunting, internships, "I should be studying but…", late nights at the library',
            'traveler'      => 'trip planning excitement, packing struggles, airport delays, hidden gems found, "you HAVE to visit X", food tourism, travel mishaps that are funny later',
            'diy_maker'     => 'home improvement projects that took 3x longer than planned, hardware store trips, "it\'s not level but I love it", woodworking, before/after projects, tool recommendations',
            'book_reader'   => 'staying up too late reading, book club arguments, library love, reading recommendations, "I cried on public transit", can\'t put it down',
            'gamer'         => 'game session recaps, "finally beat that boss", recommendations, hardware issues, gaming with friends, nostalgia games, this game is unfair',
            'music_fan'     => 'concert experiences, album deep-dives, learning an instrument, "this song has been in my head for 3 days", playlist sharing, discovering a new artist',
            'weather_watcher' => 'daily conditions commentary, storm tracking excitement, seasonal changes, "I can\'t believe it\'s this hot/cold", outdoor plans foiled by weather, cloud photos',
            'sports_fan'    => 'game reactions, "we cannot discuss last night", fantasy league drama, pickup games, watching with friends, sports bar observations, team loyalty',
        ];
        return $hints[$archetype] ?? 'everyday life';
    }

    private function describeTimePeriod(Carbon $date): string
    {
        $month = $date->month;
        $year = $date->year;
        $monthName = $date->format('F Y');

        $seasonal = match (true) {
            $month >= 11 || $month <= 1 => 'winter — cold, shorter days, holiday season vibes lingering, people are cozy indoors',
            $month >= 2 && $month <= 3  => 'late winter/early spring — everyone is SO ready for warmer weather, first crocuses appearing',
            $month >= 4 && $month <= 5  => 'spring — finally warming up, allergies, outdoor activities resuming, gardens waking up',
            $month >= 6 && $month <= 8  => 'summer — heat, vacations, outdoor everything, long days, farmers markets in full swing',
            default                     => 'fall — cooler air, leaves changing, back-to-school, pumpkin everything, harvest season',
        };

        $specific = match ($month) {
            11 => 'Thanksgiving coming up, end of year approaching',
            12 => 'holiday season, year wrapping up, last-minute shopping',
            1  => 'new year energy, resolutions (kept or already abandoned), winter cold',
            2  => 'Valentine\'s Day, Groundhog Day jokes, still winter',
            3  => 'St. Patrick\'s Day, spring preview days',
            4  => 'spring fully arriving, tax season grumbles, maybe spring break',
            5  => 'Mother\'s Day, everything in bloom',
            6  => 'start of summer, school\'s out energy',
            7  => 'peak summer, 4th of July in the US',
            8  => 'end of summer, back-to-school prep',
            9  => 'Labor Day, back to routines, first real fall feeling',
            10 => 'Halloween, peak fall colors, apple picking',
            default => '',
        };

        return "{$monthName} — {$seasonal}. {$specific}";
    }

    private function insertPosts(User $user, array $texts, Carbon $joinedAt, Carbon $now): void
    {
        if (empty($texts)) {
            return;
        }

        $totalDays = max(1, $joinedAt->diffInDays($now));
        $count = count($texts);

        // Distribute timestamps naturally — more posts recently for active users
        $timestamps = $this->distributeTimestamps($joinedAt, $now, $count, $user->posting_frequency);

        $allHashtags = Hashtag::pluck('id', 'name')->toArray();

        foreach ($texts as $i => $body) {
            if (! is_string($body) || strlen(trim($body)) < 5) {
                continue;
            }

            $body = substr(trim($body), 0, 300);
            $timestamp = $timestamps[$i] ?? $now->copy()->subDays(rand(0, $totalDays));

            $post = Post::create([
                'user_id'    => $user->id,
                'body'       => $body,
                'parent_id'  => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            // Extract and attach hashtags
            preg_match_all('/#([a-zA-Z][a-zA-Z0-9_]{1,29})/', $body, $matches);
            foreach ($matches[1] as $tag) {
                $tagLower = strtolower($tag);
                if (! isset($allHashtags[$tagLower])) {
                    $hashtag = Hashtag::create(['name' => $tagLower, 'post_count' => 0]);
                    $allHashtags[$tagLower] = $hashtag->id;
                }
                DB::table('post_hashtag')->insertOrIgnore([
                    'post_id'    => $post->id,
                    'hashtag_id' => $allHashtags[$tagLower],
                ]);
                Hashtag::where('id', $allHashtags[$tagLower])->increment('post_count');
            }
        }
    }

    private function distributeTimestamps(Carbon $start, Carbon $end, int $count, string $frequency): array
    {
        $totalSeconds = $start->diffInSeconds($end);
        $timestamps = [];

        for ($i = 0; $i < $count; $i++) {
            // Skew toward recent for active users
            $fraction = match ($frequency) {
                'power'    => pow($i / max(1, $count - 1), 0.7),
                'moderate' => pow($i / max(1, $count - 1), 0.85),
                default    => $i / max(1, $count - 1),
            };

            $seconds = (int) ($fraction * $totalSeconds);
            // Add some jitter within the day
            $jitter = rand(-3600 * 4, 3600 * 4);
            $seconds = max(0, min($totalSeconds, $seconds + $jitter));

            $ts = $start->copy()->addSeconds($seconds);
            // Realistic posting hours (7am-11pm bias)
            $hour = $ts->hour;
            if ($hour < 7) {
                $ts->addHours(rand(7, 10) - $hour);
            }

            $timestamps[] = $ts;
        }

        sort($timestamps);
        return $timestamps;
    }

    private function generateReplies(Carbon $now): void
    {
        $this->info('Generating reply threads…');

        // Pick posts that will get replies (~15% of top-level posts, favouring engaging ones)
        $posts = Post::whereNull('parent_id')
            ->with('user')
            ->inRandomOrder()
            ->limit(80)
            ->get();

        if ($posts->isEmpty()) {
            return;
        }

        $users = User::all();
        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        $allHashtags = Hashtag::pluck('id', 'name')->toArray();

        foreach ($posts as $post) {
            $bar->advance();

            $replyCount = rand(1, 4);
            $replyUsers = $users->where('id', '!=', $post->user_id)->random(min($replyCount, $users->count() - 1));

            $prompt = $this->buildReplyPrompt($post, $replyUsers->pluck('display_name')->toArray(), $replyCount);
            $replies = $this->callAi($prompt, 1500);

            if (! $replies) {
                continue;
            }

            $replyUsersList = $replyUsers->values();
            foreach ($replies as $j => $replyText) {
                if (! is_string($replyText) || strlen(trim($replyText)) < 5) {
                    continue;
                }

                $replyUser = $replyUsersList[$j % $replyUsersList->count()];
                $replyTimestamp = Carbon::parse($post->created_at)->addMinutes(rand(5, 360 * 24));
                if ($replyTimestamp->gt($now)) {
                    $replyTimestamp = $now->copy()->subMinutes(rand(1, 60));
                }

                $replyPost = Post::create([
                    'user_id'    => $replyUser->id,
                    'body'       => substr(trim($replyText), 0, 300),
                    'parent_id'  => $post->id,
                    'created_at' => $replyTimestamp,
                    'updated_at' => $replyTimestamp,
                ]);

                // Hashtags in replies
                preg_match_all('/#([a-zA-Z][a-zA-Z0-9_]{1,29})/', $replyPost->body, $matches);
                foreach ($matches[1] as $tag) {
                    $tagLower = strtolower($tag);
                    if (! isset($allHashtags[$tagLower])) {
                        $hashtag = Hashtag::create(['name' => $tagLower, 'post_count' => 0]);
                        $allHashtags[$tagLower] = $hashtag->id;
                    }
                    DB::table('post_hashtag')->insertOrIgnore([
                        'post_id'    => $replyPost->id,
                        'hashtag_id' => $allHashtags[$tagLower],
                    ]);
                }
            }

            // Update parent reply_count
            Post::where('id', $post->id)->update([
                'reply_count' => Post::where('parent_id', $post->id)->count(),
            ]);

            usleep(150000);
        }

        $bar->finish();
        $this->newLine();
    }

    private function buildReplyPrompt(Post $post, array $replierNames, int $count): string
    {
        $names = implode(', ', $replierNames);
        return <<<PROMPT
Write {$count} short replies to this MyStream post.

Original post by @{$post->user->username}:
"{$post->body}"

The replies come from different users: {$names}

Rules:
- Return ONLY a JSON array of {$count} strings
- Each reply is short (15–150 characters) and casual
- Replies can agree, sympathize, laugh, share a similar experience, ask a follow-up question, or give advice
- Feel like real human responses — specific, not generic ("omg same!" / "have you tried X?" / "lol my dog does this too")
- No politics, no mean replies, keep it light
- Don't start every reply with the same phrase
Return ONLY the JSON array.
PROMPT;
    }

    private function callAi(string $prompt, int $maxTokens = 4096): ?array
    {
        try {
            $response = Http::timeout(90)
                ->withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $this->model,
                    'max_tokens' => $maxTokens,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (! $response->successful()) {
                $this->warn('API error: ' . $response->status());
                return null;
            }

            $text = $response->json('content.0.text', '');
            $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $text = preg_replace('/\s*```$/m', '', $text);
            $text = trim($text);

            // Sometimes the model returns an object with a key
            $data = json_decode($text, true);
            if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                // Array of objects — extract text field if present
                return array_column($data, 'text') ?: array_column($data, 'content') ?: null;
            }
            if (is_array($data)) {
                return $data;
            }

            $this->warn('Could not parse JSON. Start: ' . substr($text, 0, 200));
            return null;
        } catch (\Exception $e) {
            $this->warn('Exception: ' . $e->getMessage());
            return null;
        }
    }
}
