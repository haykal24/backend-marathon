<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateEvents2014To2018 extends Command
{
    /**
     * Command untuk migrasi batch event tahun 2014-2018
     * 
     * Usage:
     * php artisan events:migrate-2014-2018
     * php artisan events:migrate-2014-2018 --status=pending_review
     * php artisan events:migrate-2014-2018 --dry-run
     * php artisan events:migrate-2014-2018 --skip-images
     * php artisan events:migrate-2014-2018 --batch-limit=100
     */
    
    protected $signature = 'events:migrate-2014-2018
                            {--status=published : Event status (published or pending_review)}
                            {--dry-run : Preview mode, do not save to database}
                            {--with-images : Upload images (default: skip-images, karena data 2014-2018 tidak ada images)}
                            {--batch-limit= : Limit number of events per year (useful for testing)}
                            {--force : Overwrite existing events}';

    protected $description = 'Migrate events from 2014-2018 folders (batch import)';

    public function handle(): int
    {
        $status = $this->option('status') ?: 'published'; // Default: published
        $dryRun = $this->option('dry-run');
        $withImages = $this->option('with-images'); // Default: false (skip images karena data 2014-2018 tidak ada images)
        $skipImages = !$withImages; // Invert: jika with-images false, maka skip-images true
        $batchLimit = $this->option('batch-limit');
        $force = $this->option('force');

        $this->info('🚀 Starting batch migration for events 2014-2018');
        $this->info("📊 Status: {$status}");
        $this->info("🖼️  Images: " . ($skipImages ? 'SKIPPED (data 2014-2018 tidak ada images)' : 'WILL UPLOAD'));
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No data will be saved');
            $this->newLine();
        }

        $years = [2014, 2015, 2016, 2017, 2018];
        $totalSuccess = 0;
        $totalSkipped = 0;
        $totalErrors = 0;
        $startTime = microtime(true);

        $progressBar = $this->output->createProgressBar(count($years));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $progressBar->start();

        foreach ($years as $index => $year) {
            $progressBar->setMessage("Processing year {$year}...");
            $progressBar->display();

            $command = "events:import-from-json";
            
            // Resolve path: base_path() = backend/, jadi untuk akses backend/dataevent dari root
            // kita perlu naik 1 level dulu: ../backend/dataevent
            // Atau gunakan path absolut langsung
            $dataEventPath = dirname(base_path()) . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'dataevent';
            
            $arguments = [
                '--path' => $dataEventPath, // Path absolut yang benar
                '--source' => 'folder',
                '--year' => (string) $year,
                '--status' => $status,
            ];

            if ($dryRun) {
                $arguments['--dry-run'] = true;
            }

            if ($skipImages) {
                $arguments['--skip-images'] = true;
            }

            if ($force) {
                $arguments['--force'] = true;
            }

            if ($batchLimit) {
                $arguments['--batch-limit'] = $batchLimit;
            }

            try {
                // Capture output untuk summary
                $exitCode = $this->call($command, $arguments);

                if ($exitCode === Command::SUCCESS) {
                    $totalSuccess++;
                    $progressBar->setMessage("✅ Year {$year} completed");
                } else {
                    $totalErrors++;
                    $progressBar->setMessage("❌ Year {$year} failed");
                }
            } catch (\Exception $e) {
                $totalErrors++;
                $progressBar->setMessage("❌ Year {$year} error: " . substr($e->getMessage(), 0, 30));
                $this->newLine();
                $this->error("Error processing year {$year}: " . $e->getMessage());
            }

            $progressBar->advance();
            
            // Add spacing between years for readability
            if ($index < count($years) - 1) {
                $this->newLine();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $elapsedTime = round(microtime(true) - $startTime, 2);
        $this->info('📊 Batch Migration Summary:');
        $this->line("  ✅ Success: {$totalSuccess} years");
        $this->line("  ⚠️  Skipped: {$totalSkipped} years");
        $this->line("  ❌ Errors: {$totalErrors} years");
        $this->line("  ⏱️  Time: {$elapsedTime}s");
        $this->newLine();

        if ($totalErrors === 0 && $totalSuccess > 0) {
            $this->info('🎉 All years migrated successfully!');
            return Command::SUCCESS;
        }

        if ($totalErrors > 0) {
            $this->error('⚠️  Some years failed. Check logs above.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}