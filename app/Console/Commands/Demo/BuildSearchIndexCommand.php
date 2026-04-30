<?php

namespace App\Console\Commands\Demo;

use App\Models\Post;
use Illuminate\Console\Command;

class BuildSearchIndexCommand extends Command
{
    protected $signature = 'demo:build-search-index {--memory-budget=balanced}';
    protected $description = 'Build the Scolta search index over all posts';

    public function handle(): int
    {
        $topLevel = Post::whereNull('parent_id')->count();
        $this->info("Building Scolta search index over {$topLevel} posts…");
        $this->info('Indexing: post body + author + hashtags (replies excluded).');

        $exitCode = $this->call('scolta:build', [
            '--memory-budget' => $this->option('memory-budget'),
            '--force'         => true,
        ]);

        if ($exitCode === 0) {
            $outputDir = config('scolta.pagefind.output_dir', public_path('scolta-pagefind'));
            $indexExists = file_exists($outputDir.'/pagefind-entry.json');
            if ($indexExists) {
                $this->info("✓ Search index built at {$outputDir}");
                $this->info('  Commit public/scolta-pagefind/ and db/dump.sql.gz together.');
            } else {
                $this->warn("Build reported success but index not found at {$outputDir}");
                $this->warn("Expected: {$outputDir}/pagefind-entry.json");
            }
        }

        return $exitCode;
    }
}
