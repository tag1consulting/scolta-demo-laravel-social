<?php

namespace App\Console\Commands\Demo;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateUsersCommand extends Command
{
    protected $signature = 'demo:generate-users {--fresh : Drop all existing users first}';
    protected $description = 'Generate ~100 demo users with archetypes for MyStream';

    private string $apiKey;
    private string $model = 'claude-haiku-4-5-20251001';

    public function handle(): int
    {
        $this->apiKey = env('SCOLTA_API_KEY', env('ANTHROPIC_API_KEY', ''));
        if (! $this->apiKey) {
            $this->error('SCOLTA_API_KEY or ANTHROPIC_API_KEY must be set.');
            return 1;
        }

        if ($this->option('fresh')) {
            User::query()->delete();
            $this->line('Cleared existing users.');
        }

        if (User::count() >= 90) {
            $this->info('Users already generated (' . User::count() . '). Use --fresh to regenerate.');
            return 0;
        }

        $this->info('Generating 100 MyStream users (2 batches of 50)…');

        $users = [];
        foreach ([1, 2] as $batch) {
            $this->line("  Batch {$batch}/2…");
            $prompt = $this->buildPrompt($batch);
            $batchUsers = $this->callAi($prompt);
            if (! $batchUsers) {
                $this->error("Failed batch {$batch}.");
                return 1;
            }
            $users = array_merge($users, $batchUsers);
            usleep(500000);
        }

        if (empty($users)) {
            $this->error('Failed to generate users from AI.');
            return 1;
        }

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        $now = Carbon::create(2026, 4, 30);

        foreach ($users as $data) {
            $monthsActive = (int) ($data['months_active'] ?? rand(1, 6));
            $joinedAt = $now->copy()->subMonths($monthsActive)->subDays(rand(0, 20));

            $username = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-', '.'], '_', $data['username'] ?? '')));
            if (! $username) {
                $username = 'user_' . uniqid();
            }
            // Ensure uniqueness
            $base = $username;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . $i++;
            }

            User::create([
                'display_name'       => $data['display_name'] ?? 'Unknown',
                'username'           => $username,
                'bio'                => $data['bio'] ?? '',
                'avatar_url'         => "https://api.dicebear.com/9.x/bottts-neutral/svg?seed={$username}&backgroundColor=b6e3f4,c0aede,d1d4f9",
                'joined_at'          => $joinedAt,
                'archetype'          => $data['archetype'] ?? 'commuter',
                'secondary_interest' => $data['secondary_interest'] ?? null,
                'posting_frequency'  => $data['posting_frequency'] ?? 'moderate',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Created ' . User::count() . ' users.');
        return 0;
    }

    private function buildPrompt(int $batch): string
    {
        $archetypes = 'pet_parent, home_cook, fitness, new_parent, gardener, commuter, remote_worker, student, traveler, diy_maker, book_reader, gamer, music_fan, weather_watcher, sports_fan';

        $batch1Freq = 'power: 10, moderate: 22, casual: 18';
        $batch2Freq = 'power: 10, moderate: 23, casual: 17';
        $freqNote = $batch === 1 ? $batch1Freq : $batch2Freq;

        $nameHint = $batch === 1
            ? 'Anglo, Hispanic/Latino, South Asian, East Asian names'
            : 'African, Middle Eastern, Scandinavian, Eastern European, West African, Filipino, Brazilian names';

        return <<<PROMPT
Generate exactly 50 diverse social media users for MyStream (batch {$batch} of 2). Return ONLY valid JSON — a single array of 50 objects. No markdown, no explanation.

Each object: display_name, username, bio, archetype, secondary_interest, posting_frequency, months_active

Rules:
- display_name: Real-sounding full name. This batch: {$nameHint}
- username: lowercase, letters/numbers/underscores only, max 20 chars, creative and personal
- bio: 1 specific sentence (max 120 chars). Name a pet, hobby quirk, or life detail. Casual voice.
- archetype: one of: {$archetypes}
- secondary_interest: different archetype from the list
- posting_frequency: power / moderate / casual. This batch: {$freqNote}
- months_active: 1-6 (power users tend to be 4-6)

Ensure at least 3 users per archetype across this batch. Make each person distinct.

Return ONLY the JSON array of 50 objects.
PROMPT;
    }

    private function callAi(string $prompt): ?array
    {
        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $this->model,
                    'max_tokens' => 8192,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (! $response->successful()) {
                $this->error('API error: ' . $response->status() . ' ' . $response->body());
                return null;
            }

            $text = $response->json('content.0.text', '');
            // Strip markdown fences if the model added them despite instructions
            $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $text = preg_replace('/\s*```$/m', '', $text);
            $text = trim($text);

            $data = json_decode($text, true);
            if (! is_array($data)) {
                $this->error('Could not parse JSON. Response: ' . substr($text, 0, 500));
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            return null;
        }
    }
}
