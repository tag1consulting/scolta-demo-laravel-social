<?php

namespace App\Console\Commands\Demo;

use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInteractionsCommand extends Command
{
    protected $signature = 'demo:generate-interactions';
    protected $description = 'Assign realistic star/boost/reply counts to posts';

    public function handle(): int
    {
        $total = Post::count();
        if ($total === 0) {
            $this->error('No posts found. Run demo:generate-posts first.');
            return 1;
        }

        $this->info("Assigning interaction counts to {$total} posts…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $now = Carbon::create(2026, 4, 30);

        Post::chunk(500, function ($posts) use ($bar, $now) {
            foreach ($posts as $post) {
                // Age factor: older posts have had more time to accumulate stars
                $ageInDays = max(0, Carbon::parse($post->created_at)->diffInDays($now));
                $ageFactor = min(1.0, $ageInDays / 60); // Caps at 60 days

                // Replies already have their reply_count set; top-level posts get stars/boosts
                if ($post->parent_id !== null) {
                    // Replies get fewer stars
                    $stars = $this->rollStars(0, 8, $ageFactor, 0.6);
                    $post->update([
                        'star_count'  => $stars,
                        'boost_count' => 0,
                        'reply_count' => 0,
                    ]);
                } else {
                    // Viral lottery: ~3% of posts go viral
                    $isViral = (rand(1, 100) <= 3);
                    // Popular tier: ~10% of posts
                    $isPopular = ! $isViral && (rand(1, 100) <= 10);

                    if ($isViral) {
                        $stars = $this->rollStars(40, 180, $ageFactor, 1.0);
                        $boosts = (int) ($stars * rand(15, 40) / 100);
                    } elseif ($isPopular) {
                        $stars = $this->rollStars(12, 55, $ageFactor, 1.0);
                        $boosts = (int) ($stars * rand(10, 30) / 100);
                    } else {
                        $stars = $this->rollStars(0, 14, $ageFactor, 0.8);
                        $boosts = $stars > 3 ? rand(0, (int) ($stars * 0.3)) : 0;
                    }

                    // reply_count is already set by generate-posts for posts that have replies;
                    // posts without replies keep 0
                    $post->update([
                        'star_count'  => $stars,
                        'boost_count' => $boosts,
                    ]);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('✓ Interactions assigned.');

        // Stats
        $avgStars = Post::whereNull('parent_id')->avg('star_count');
        $maxStars = Post::whereNull('parent_id')->max('star_count');
        $this->line("  Avg stars (top-level): " . round($avgStars, 1));
        $this->line("  Max stars (viral post): {$maxStars}");

        return 0;
    }

    private function rollStars(int $min, int $max, float $ageFactor, float $chance): int
    {
        if (rand(1, 100) > ($chance * 100)) {
            return 0;
        }
        $base = rand($min, $max);
        // Scale down for very new posts
        return (int) max(0, $base * (0.3 + 0.7 * $ageFactor));
    }
}
