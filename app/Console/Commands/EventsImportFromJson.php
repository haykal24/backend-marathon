<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventType;
use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EventsImportFromJson extends Command
{
    protected $signature = 'events:import-from-json
                            {--path=backend/dataevent : Path to data event folder}
                            {--source=index : Source format (index or folder)}
                            {--status=published : Event status after import (published or pending_review)}
                            {--year= : Filter by single year when using folder source (e.g., 2025)}
                            {--year-start= : Start year for range import (inclusive)}
                            {--year-end= : End year for range import (inclusive)}
                            {--limit= : Legacy limit option (alias of batch-limit)}
                            {--batch-limit= : Limit number of events processed per run (useful for periodic migration)}
                            {--dry-run : Preview mode, do not save to database}
                            {--skip-images : Skip image upload process}
                            {--force : Overwrite existing events (by slug or duplicate title/date)}';

    protected $description = 'Import events from JSON files in dataevent folder';

    protected int $successCount = 0;
    protected int $skippedCount = 0;
    protected int $errorCount = 0;
    protected int $imageProcessedCount = 0;
    protected array $processedEventKeys = [];

    public function handle(): int
    {
        $path = $this->option('path');
        $source = $this->option('source');
        $status = $this->option('status');
        $dryRun = $this->option('dry-run');
        $skipImages = $this->option('skip-images');
        $force = $this->option('force');

        if (!str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:/', $path)) {
            $path = base_path($path);
        }

        $this->info("Importing events from: {$path}");
        $this->info("Source format: {$source}");
        $this->info("Status: {$status}");
        $this->info("Dry run: " . ($dryRun ? 'YES' : 'NO'));
        $this->newLine();

        if (!File::exists($path)) {
            $this->error("Path not found: {$path}");
            return Command::FAILURE;
        }

        $year = $this->option('year');
        $yearStart = $this->option('year-start');
        $yearEnd = $this->option('year-end');
        $limitOption = $this->option('limit');
        $batchLimitOption = $this->option('batch-limit');
        $batchLimit = null;
        if ($batchLimitOption !== null && $batchLimitOption !== '') {
            $batchLimit = (int) $batchLimitOption;
        } elseif ($limitOption !== null && $limitOption !== '') {
            $batchLimit = (int) $limitOption;
        }
        
        $events = match ($source) {
            'index' => $this->readFromIndex($path),
            'folder' => $this->readFromFolders($path, $year, $yearStart, $yearEnd),
            default => throw new \InvalidArgumentException("Invalid source: {$source}"),
        };

        if (empty($events)) {
            $this->error('No events found to import');
            return Command::FAILURE;
        }

        // Apply limit if specified
        if ($batchLimit !== null && $batchLimit > 0) {
            $events = array_slice($events, 0, $batchLimit);
            $this->info("Batch limit applied: importing only {$batchLimit} events this run");
        }

        $total = count($events);
        $this->info("Found {$total} events to import");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($events as $eventData) {
            try {
                $this->processEvent($eventData, $path, $status, $dryRun, $skipImages, $force);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->newLine();
                $this->error("Error processing event: " . ($eventData['judul'] ?? 'Unknown') . " - " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->displaySummary($total);

        return Command::SUCCESS;
    }

    protected function readFromIndex(string $path): array
    {
        $indexFile = $path . '/hasil_index.json';
        
        if (!File::exists($indexFile)) {
            throw new \Exception("Index file not found: {$indexFile}");
        }

        $content = File::get($indexFile);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in index file: " . json_last_error_msg());
        }

        return $data ?? [];
    }

    protected function readFromFolders(string $path, ?string $year = null, ?string $yearStart = null, ?string $yearEnd = null): array
    {
        $events = [];
        $directories = File::directories($path);
        $yearStart = $yearStart ? (int) $yearStart : null;
        $yearEnd = $yearEnd ? (int) $yearEnd : null;

        foreach ($directories as $directory) {
            $dirName = basename($directory);

            // Support both "file 2025" and "2025" directory naming conventions
            if (!preg_match('/^(?:file[\s_-]*)?(\d{4})$/i', $dirName, $matches)) {
                continue;
            }

            if ($year !== null && $matches[1] !== $year) {
                continue;
            }

            $numericYear = (int) $matches[1];
            if ($yearStart !== null && $numericYear < $yearStart) {
                continue;
            }
            if ($yearEnd !== null && $numericYear > $yearEnd) {
                continue;
            }

            $jsonFiles = File::glob($directory . '/*.json');

            foreach ($jsonFiles as $jsonFile) {
                $content = File::get($jsonFile);
                $data = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE && $data) {
                    $events[] = $data;
                }
            }
        }

        return $events;
    }

    protected function processEvent(array $data, string $basePath, string $status, bool $dryRun, bool $skipImages, bool $force): void
    {
        $required = ['judul', 'lokasi', 'kota', 'tanggal_event'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        $slug = $this->generateUniqueSlug($data['judul'], $force);

        // Extract provinsi dari lokasi
        $provinceName = $this->extractProvinceFromLocation($data['lokasi'], $data['kota']);

        // Normalize event type dan create/get EventType
        $eventTypeSlug = $this->normalizeEventType($data['jenis_event'] ?? 'road run');
        $eventType = $this->getOrCreateEventType($eventTypeSlug, $data['jenis_event'] ?? 'road run');

        // Create/get Province
        $province = $this->getOrCreateProvince($provinceName);

        try {
            $eventDate = \Carbon\Carbon::parse($data['tanggal_event']);
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: {$data['tanggal_event']}");
        }

        if ($dryRun) {
            return;
        }

        $eventKey = $this->generateEventKey($data['judul'], $eventDate);

        if (!$force) {
            if (isset($this->processedEventKeys[$eventKey])) {
                $this->skippedCount++;
                $this->line("Skipping duplicate in import batch: {$data['judul']} ({$eventDate->toDateString()})");
                return;
            }

            $duplicateInDb = Event::where('title', $data['judul'])
                ->whereDate('event_date', $eventDate->toDateString())
                ->first();

            if ($duplicateInDb) {
                $this->skippedCount++;
                $this->line("Skipping existing event in DB: {$data['judul']} ({$eventDate->toDateString()}) [ID {$duplicateInDb->id}]");
                return;
            }

            if (Event::where('slug', $slug)->exists()) {
                $this->skippedCount++;
                $this->line("Skipping because slug already exists: {$slug}");
                return;
            }
        }

        if ($force) {
            $existingDuplicate = Event::where('title', $data['judul'])
                ->whereDate('event_date', $eventDate->toDateString())
                ->first();

            if ($existingDuplicate) {
                $slug = $existingDuplicate->slug;
            }
        }

        DB::beginTransaction();

        try {
            $event = Event::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => null,
                    'title' => $data['judul'],
                    'description' => $data['isi_informasi'] ?? null,
                    'location_name' => $data['lokasi'],
                    'city' => $data['kota'],
                    'province' => $province?->name,
                    'event_date' => $eventDate,
                    'event_end_date' => null,
                    'event_type' => $eventTypeSlug,
                    'organizer_name' => null,
                    'registration_url' => null,
                    'benefits' => $data['benefit_peserta'] ?? null,
                    'contact_info' => $data['kontak_event'] ?? null,
                    'registration_fees' => $this->normalizeRegistrationFees($data['biaya_registrasi'] ?? null),
                    'social_media' => $this->buildSocialMedia($data['kontak_event'] ?? []),
                    'status' => $status,
                ]
            );

            if (!empty($data['kategori']) && is_array($data['kategori'])) {
                $categoryIds = [];
                foreach ($data['kategori'] as $categoryName) {
                    $category = EventCategory::firstOrCreate(
                        ['slug' => Str::slug($categoryName)],
                        ['name' => $categoryName]
                    );
                    $categoryIds[] = $category->id;
                }
                $event->categories()->sync($categoryIds);
            }

            if (!$skipImages && !empty($data['image'])) {
                $imagesDir = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
                $imagePath = $this->findImageFile($imagesDir, $data['image']);
                
                if ($imagePath && File::exists($imagePath)) {
                    try {
                        $event->clearMediaCollection('default');
                        
                        $pathInfo = pathinfo($imagePath);
                        $extension = strtolower($pathInfo['extension'] ?? '');
                        $filename = $pathInfo['filename'];
                        
                        // Copy to temp location to preserve original file
                        $tempPath = storage_path('app/temp_event_image_' . $event->id . '_' . time() . '_' . uniqid() . '.' . ($pathInfo['extension'] ?? 'jpg'));
                        File::copy($imagePath, $tempPath);
                        
                        try {
                            $media = $event->addMedia($tempPath)
                            ->usingName($filename)
                            ->usingFileName($pathInfo['basename'])
                            ->toMediaCollection('default');
                            
                        if ($extension !== 'webp' && $media && !$media->hasGeneratedConversion('webp')) {
                            $media->performConversions();
                        }
                            
                        $this->imageProcessedCount++;
                        } finally {
                            // Clean up temp file
                            if (File::exists($tempPath)) {
                                File::delete($tempPath);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing image for event {$event->id}: " . $e->getMessage());
                    }
                } else {
                    $this->warn("Image file not found: {$data['image']} (searched in: {$imagesDir})");
                }
            }

            DB::commit();
            $this->processedEventKeys[$eventKey] = true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generateEventKey(string $title, \Carbon\Carbon $eventDate): string
    {
        return strtolower(trim($title)) . '|' . $eventDate->toDateString();
    }

    /**
     * Extract province name from location string
     * Contoh: "Ammaia Ecoforest Gallery, Jl. Suvarna Sutera Boulevard, Tangerang, Banten"
     * Akan return: "Banten"
     */
    protected function extractProvinceFromLocation(string $lokasi, string $kota): ?string
    {
        if (empty($lokasi)) {
            return null;
        }

        // Split by comma
        $parts = array_map('trim', explode(',', $lokasi));
        $parts = array_filter($parts);

        if (empty($parts)) {
            return null;
        }

        // List provinsi di Indonesia
        $provinces = [
            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi',
            'Sumatera Selatan', 'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung',
            'Kepulauan Riau', 'DKI Jakarta', 'Jakarta', 'Jawa Barat', 'Jawa Tengah',
            'DI Yogyakarta', 'Daerah Istimewa Yogyakarta', 'Yogyakarta',
            'Jawa Timur', 'Banten', 'Bali', 'Nusa Tenggara Barat', 'NTB',
            'Nusa Tenggara Timur', 'NTT', 'Kalimantan Barat', 'Kalimantan Tengah',
            'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
            'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara',
            'Gorontalo', 'Sulawesi Barat', 'Maluku', 'Maluku Utara',
            'Papua', 'Papua Barat', 'Papua Selatan', 'Papua Pegunungan',
            'Papua Tengah', 'Papua Barat Daya',
        ];

        // Cek dari belakang (biasanya provinsi di akhir)
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $part = trim($parts[$i]);
            
            // Skip jika sama dengan kota
            if (strcasecmp($part, $kota) === 0) {
                continue;
            }

            // Normalize untuk matching
            $normalized = $this->normalizeProvinceName($part);

            // Cek exact match
            foreach ($provinces as $province) {
                if (strcasecmp($normalized, $this->normalizeProvinceName($province)) === 0) {
                    return $province; // Return nama standar
                }
            }

            // Cek partial match (untuk "DKI Jakarta" -> "Jakarta")
            foreach ($provinces as $province) {
                if (stripos($normalized, $this->normalizeProvinceName($province)) !== false ||
                    stripos($this->normalizeProvinceName($province), $normalized) !== false) {
                    return $province;
                }
            }
        }

        // Fallback: cek dari mapping kota -> provinsi
        return $this->getProvinceFromCity($kota);
    }

    /**
     * Normalize province name untuk matching
     */
    protected function normalizeProvinceName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/^(dki|di|daerah istimewa)\s+/i', '', $name);
        $name = preg_replace('/\s+(provinsi|prov\.?)$/i', '', $name);
        return $name;
    }

    /**
     * Get province from city mapping (fallback)
     */
    protected function getProvinceFromCity(string $city): ?string
    {
        $cityProvinceMap = [
            'Jakarta' => 'DKI Jakarta',
            'Jakarta Barat' => 'DKI Jakarta',
            'Jakarta Timur' => 'DKI Jakarta',
            'Jakarta Selatan' => 'DKI Jakarta',
            'Jakarta Utara' => 'DKI Jakarta',
            'Jakarta Pusat' => 'DKI Jakarta',
            'Bandung' => 'Jawa Barat',
            'Bogor' => 'Jawa Barat',
            'Depok' => 'Jawa Barat',
            'Tangerang' => 'Banten',
            'Tangerang Selatan' => 'Banten',
            'Serang' => 'Banten',
            'Surabaya' => 'Jawa Timur',
            'Malang' => 'Jawa Timur',
            'Semarang' => 'Jawa Tengah',
            'Yogyakarta' => 'DI Yogyakarta',
            'Denpasar' => 'Bali',
            'Medan' => 'Sumatera Utara',
            'Palembang' => 'Sumatera Selatan',
            'Makassar' => 'Sulawesi Selatan',
        ];

        // Exact match
        if (isset($cityProvinceMap[$city])) {
            return $cityProvinceMap[$city];
        }

        // Partial match
        foreach ($cityProvinceMap as $mapCity => $province) {
            if (stripos($city, $mapCity) !== false || stripos($mapCity, $city) !== false) {
                return $province;
            }
        }

        return null;
    }

    /**
     * Get or create EventType from jenis_event
     */
    protected function getOrCreateEventType(string $slug, string $originalName): EventType
    {
        $nameMap = [
            'road_run' => 'Road Run',
            'trail_run' => 'Trail Run',
            'fun_run' => 'Fun Run',
            'virtual_run' => 'Virtual Run',
            'marathon' => 'Marathon',
            'half_marathon' => 'Half Marathon',
        ];

        $name = $nameMap[$slug] ?? ucwords(str_replace('_', ' ', $slug));

        return EventType::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => "Event jenis {$name}",
                'is_active' => true,
            ]
        );
    }

    /**
     * Get or create Province
     */
    protected function getOrCreateProvince(?string $provinceName): ?Province
    {
        if (empty($provinceName)) {
            return null;
        }

        $slug = Str::slug($provinceName);

        return Province::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $provinceName,
                'description' => "Provinsi {$provinceName}",
                'is_active' => true,
            ]
        );
    }

    protected function generateUniqueSlug(string $title, bool $force): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (!$force && Event::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function normalizeEventType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'road run', 'roadrun' => 'road_run',
            'trail run', 'trailrun' => 'trail_run',
            'fun run', 'funrun' => 'fun_run',
            'virtual run', 'virtualrun' => 'virtual_run',
            'marathon' => 'marathon',
            'half marathon', 'half_marathon' => 'half_marathon',
            default => 'road_run',
        };
    }

    protected function normalizeRegistrationFees($fees): ?array
    {
        if (is_null($fees)) {
            return null;
        }

        if (is_string($fees)) {
            return null;
        }

        if (is_array($fees)) {
            return $fees;
        }

        return null;
    }

    protected function findImageFile(string $imagesDir, string $imageName): ?string
    {
        if (!is_dir($imagesDir)) {
            return null;
        }

        $imagesDir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagesDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $imageName = trim($imageName);
        
        $exactPath = $imagesDir . $imageName;
        if (File::exists($exactPath)) {
            return realpath($exactPath) ?: $exactPath;
        }

        $allFiles = File::files($imagesDir);
        $imageNameLower = strtolower($imageName);
        
        foreach ($allFiles as $file) {
            $fileName = $file->getFilename();
            if (strtolower($fileName) === $imageNameLower) {
                return $file->getRealPath() ?: $file->getPathname();
            }
        }

        $pathInfo = pathinfo($imageName);
        $baseName = $pathInfo['filename'];
        $jsonExtension = strtolower($pathInfo['extension'] ?? '');

        if (preg_match('/^(.+)_(\d{4})_(\d{4}-\d{2}-\d{2})$/', $baseName, $matches)) {
            $eventName = $matches[1];
            $datePart = $matches[3];
            $alternativeName = $eventName . '_' . $datePart;
            $alternativeNameLower = strtolower($alternativeName);
            
            $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!empty($jsonExtension)) {
                $extensions = array_unique(array_merge([$jsonExtension], $extensions));
            }
            
            foreach ($allFiles as $file) {
                $fileName = $file->getFilename();
                $filePathInfo = pathinfo($fileName);
                $fileBaseName = $filePathInfo['filename'];
                $fileExtension = strtolower($filePathInfo['extension'] ?? '');
                
                if (strtolower($fileBaseName) === $alternativeNameLower) {
                    if (in_array($fileExtension, $extensions)) {
                        return $file->getRealPath() ?: $file->getPathname();
                    }
                }
            }
        }

        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!empty($jsonExtension)) {
            $extensions = array_unique(array_merge([$jsonExtension], $extensions));
        }
        
        foreach ($allFiles as $file) {
            $fileName = $file->getFilename();
            $filePathInfo = pathinfo($fileName);
            $fileBaseName = $filePathInfo['filename'];
            $fileExtension = strtolower($filePathInfo['extension'] ?? '');
            
            if (strtolower($fileBaseName) === strtolower($baseName)) {
                if (in_array($fileExtension, $extensions)) {
                    return $file->getRealPath() ?: $file->getPathname();
                }
            }
        }

        $baseNameLower = strtolower($baseName);
        foreach ($allFiles as $file) {
            $fileName = $file->getFilename();
            $filePathInfo = pathinfo($fileName);
            $fileBaseName = $filePathInfo['filename'];
            $fileExtension = strtolower($filePathInfo['extension'] ?? '');
            
            if (strtolower($fileBaseName) === $baseNameLower || 
                (strlen($baseNameLower) > 10 && 
                 similar_text($baseNameLower, strtolower($fileBaseName)) / max(strlen($baseNameLower), strlen($fileBaseName)) > 0.9)) {
                if (in_array($fileExtension, $extensions)) {
                    return $file->getRealPath() ?: $file->getPathname();
                }
            }
        }

        return null;
    }

    protected function buildSocialMedia(array $kontakEvent): ?array
    {
        $socialMedia = [];

        if (!empty($kontakEvent['Instagram'])) {
            $socialMedia['IG'] = is_array($kontakEvent['Instagram']) 
                ? $kontakEvent['Instagram'] 
                : [$kontakEvent['Instagram']];
        }

        return !empty($socialMedia) ? $socialMedia : null;
    }

    protected function displaySummary(int $total): void
    {
        $this->info('Summary:');
        $this->line("  ✓ Success: {$this->successCount} events");
        $this->line("  ⚠ Skipped: {$this->skippedCount} events");
        $this->line("  ✗ Errors: {$this->errorCount} events");
        $this->newLine();
        $this->info("Images processed: {$this->imageProcessedCount}/{$total}");
        
        if ($this->successCount > 0) {
            $categories = EventCategory::count();
            $eventTypes = EventType::count();
            $provinces = Province::count();
            $this->info("Categories created: {$categories}");
            $this->info("Event Types created: {$eventTypes}");
            $this->info("Provinces created: {$provinces}");
        }
    }
}