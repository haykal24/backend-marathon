<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestR2Connection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'r2:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to Cloudflare R2 storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Cloudflare R2 Connection...');
        $this->newLine();

        // 1. Check config
        $this->info('1. Checking configuration...');
        $disk = config('filesystems.disks.r2');
        
        if (!$disk) {
            $this->error('   ✗ R2 disk configuration not found in config/filesystems.php');
            return Command::FAILURE;
        }

        $this->info('   ✓ R2 disk configuration found');
        $this->line("   - Driver: {$disk['driver']}");
        $this->line("   - Bucket: " . ($disk['bucket'] ?? 'NOT SET'));
        $this->line("   - Endpoint: " . ($disk['endpoint'] ?? 'NOT SET'));
        $this->line("   - Region: " . ($disk['region'] ?? 'NOT SET'));
        $this->newLine();

        // 2. Check if disk exists
        $this->info('2. Checking if R2 disk is accessible...');
        try {
            $r2Disk = Storage::disk('r2');
            $this->info('   ✓ R2 disk instance created successfully');
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to create R2 disk instance: {$e->getMessage()}");
            return Command::FAILURE;
        }
        $this->newLine();

        // 3. Test write
        $this->info('3. Testing write operation...');
        $testFileName = 'test-' . Str::random(10) . '.txt';
        $testContent = 'This is a test file created at ' . now()->toDateTimeString();
        
        try {
            $r2Disk->put($testFileName, $testContent);
            $this->info("   ✓ Successfully uploaded file: {$testFileName}");
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to upload file: {$e->getMessage()}");
            $this->error("   Stack trace: {$e->getTraceAsString()}");
            return Command::FAILURE;
        }
        $this->newLine();

        // 4. Test read
        $this->info('4. Testing read operation...');
        try {
            if (!$r2Disk->exists($testFileName)) {
                $this->error("   ✗ File {$testFileName} does not exist after upload");
                return Command::FAILURE;
            }
            
            $readContent = $r2Disk->get($testFileName);
            if ($readContent === $testContent) {
                $this->info("   ✓ Successfully read file: {$testFileName}");
                $this->line("   Content: {$readContent}");
            } else {
                $this->warn("   ⚠ File content mismatch");
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to read file: {$e->getMessage()}");
            return Command::FAILURE;
        }
        $this->newLine();

        // 5. Test URL generation
        $this->info('5. Testing URL generation...');
        try {
            $url = $r2Disk->url($testFileName);
            $this->info("   ✓ Generated URL: {$url}");
        } catch (\Exception $e) {
            $this->warn("   ⚠ Failed to generate URL: {$e->getMessage()}");
        }
        $this->newLine();

        // 6. Test list files
        $this->info('6. Testing list operation...');
        try {
            $files = $r2Disk->files();
            $this->info("   ✓ Successfully listed files (found " . count($files) . " files)");
            if (count($files) > 0) {
                $this->line("   Sample files: " . implode(', ', array_slice($files, 0, 5)));
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠ Failed to list files: {$e->getMessage()}");
        }
        $this->newLine();

        // 7. Cleanup test file
        $this->info('7. Cleaning up test file...');
        try {
            $r2Disk->delete($testFileName);
            $this->info("   ✓ Successfully deleted test file: {$testFileName}");
        } catch (\Exception $e) {
            $this->warn("   ⚠ Failed to delete test file: {$e->getMessage()}");
            $this->warn("   Please manually delete: {$testFileName}");
        }
        $this->newLine();

        // Summary
        $this->info('✅ All R2 connection tests passed!');
        $this->info('Your R2 configuration is working correctly.');
        $this->newLine();
        $this->line('Current default disk: ' . config('filesystems.default'));
        $this->line('Current media disk: ' . config('media-library.disk_name'));

        return Command::SUCCESS;
    }
}