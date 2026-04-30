<?php

namespace App\Console\Commands\Demo;

use Illuminate\Console\Command;

class ExportCommand extends Command
{
    protected $signature = 'demo:export';
    protected $description = 'Export the database to db/dump.sql.gz for commit';

    public function handle(): int
    {
        $this->info('Exporting database via ddev export-db…');

        $outFile = base_path('db/dump.sql.gz');

        passthru('ddev export-db --gzip --file=' . escapeshellarg($outFile) . ' 2>&1', $exitCode);

        if ($exitCode !== 0 || ! file_exists($outFile) || filesize($outFile) < 1000) {
            $this->error('Export failed. Run manually: ddev export-db --gzip --file=db/dump.sql.gz');
            return 1;
        }

        $size = round(filesize($outFile) / 1024, 1);
        $this->info("✓ Exported to db/dump.sql.gz ({$size} KB)");
        $this->info('  Next: git add db/dump.sql.gz public/scolta-pagefind/ && git commit');
        return 0;
    }
}
