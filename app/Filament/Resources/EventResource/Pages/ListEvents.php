<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Helpers\ContentCleaner;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventType;
use App\Models\Province;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import_json')
                ->label('Import dari JSON')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->form([
                    Components\FileUpload::make('json_file')
                        ->label('File JSON Event')
                        ->disk('r2')
                        ->directory('imports/events')
                        ->acceptedFileTypes(['application/json', 'text/json'])
                        ->maxSize(102400) // 100 MB
                        ->required()
                        ->helperText('Upload file JSON berisi data event. Format bisa single object atau array of objects.'),
                    
                    Components\Select::make('status')
                        ->label('Status Event')
                        ->options([
                            'published' => 'Published',
                            'pending_review' => 'Pending Review',
                            'draft' => 'Draft',
                        ])
                        ->default('pending_review')
                        ->required()
                        ->helperText('Status event setelah di-import'),
                    
                    Components\Toggle::make('skip_duplicates')
                        ->label('Lewati Duplikat')
                        ->default(true)
                        ->helperText('Skip event dengan judul dan tanggal yang sama'),
                    
                    Components\Toggle::make('skip_images')
                        ->label('Lewati Upload Gambar')
                        ->default(false)
                        ->helperText('Jika aktif, gambar tidak akan di-upload'),
                ])
                ->action(function (array $data) {
                    $jsonFile = $data['json_file'];
                    
                    try {
                        // Read JSON file from R2
                        $disk = config('filesystems.default', 'r2');
                        $jsonContent = Storage::disk($disk)->get($jsonFile);
                        $jsonData = json_decode($jsonContent, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('Invalid JSON: ' . json_last_error_msg());
                        }
                        
                        // Normalize to array of events
                        $events = [];
                        if (isset($jsonData[0]) && is_array($jsonData[0])) {
                            // Array of events
                            $events = $jsonData;
                        } else {
                            // Single event object
                            $events = [$jsonData];
                        }
                        
                        $successCount = 0;
                        $skippedCount = 0;
                        $errorCount = 0;
                        $categoriesCreated = [];
                        $typesCreated = [];
                        
                        foreach ($events as $eventData) {
                            try {
                                // Validate required fields
                                $required = ['judul', 'lokasi', 'kota', 'tanggal_event'];
                                foreach ($required as $field) {
                                    if (empty($eventData[$field])) {
                                        throw new \Exception("Missing field: {$field}");
                                    }
                                }
                                
                                // Parse title
                                $title = trim($eventData['judul']);
                                $slug = Str::slug($title);
                                
                                // Check for duplicates
                                if ($data['skip_duplicates']) {
                                    $eventDate = \Carbon\Carbon::parse($eventData['tanggal_event']);
                                    
                                    $duplicate = Event::where('title', $title)
                                        ->whereDate('event_date', $eventDate->toDateString())
                                        ->first();
                                    
                                    if ($duplicate) {
                                        $skippedCount++;
                                        continue;
                                    }
                                    
                                    if (Event::where('slug', $slug)->exists()) {
                                        // Generate unique slug
                                        $originalSlug = $slug;
                                        $counter = 1;
                                        while (Event::where('slug', $slug)->exists()) {
                                            $slug = $originalSlug . '-' . $counter;
                                            $counter++;
                                        }
                                    }
                                }
                                
                                // Extract province from location
                                $provinceName = $this->extractProvinceFromLocation(
                                    $eventData['lokasi'], 
                                    $eventData['kota']
                                );
                                
                                // Get or create Province
                                $province = null;
                                if ($provinceName) {
                                    $provinceSlug = Str::slug($provinceName);
                                    $province = Province::firstOrCreate(
                                        ['slug' => $provinceSlug],
                                        [
                                            'name' => $provinceName,
                                            'description' => "Provinsi {$provinceName}",
                                            'is_active' => true,
                                        ]
                                    );
                                }
                                
                                // Normalize event type
                                $eventTypeSlug = $this->normalizeEventType($eventData['jenis_event'] ?? 'road run');
                                
                                // Get or create EventType
                                $eventType = $this->getOrCreateEventType($eventTypeSlug);
                                if ($eventType->wasRecentlyCreated) {
                                    $typesCreated[] = $eventType->name;
                                }
                                
                                // Parse date
                                $eventDate = \Carbon\Carbon::parse($eventData['tanggal_event']);
                                
                                // Clean description
                                $description = $eventData['isi_informasi'] ?? null;
                                if ($description) {
                                    $description = ContentCleaner::clean($description);
                                }
                                
                                // Normalize benefits (ensure it's an array or null)
                                $benefits = null;
                                if (isset($eventData['benefit_peserta'])) {
                                    if (is_array($eventData['benefit_peserta']) && !empty($eventData['benefit_peserta'])) {
                                        // Filter out empty values
                                        $benefits = array_values(array_filter($eventData['benefit_peserta'], fn($item) => !empty(trim($item))));
                                        $benefits = !empty($benefits) ? $benefits : null;
                                    }
                                }
                                
                                // Create event
                                DB::beginTransaction();
                                
                                $event = Event::updateOrCreate(
                                    ['slug' => $slug],
                                    [
                                        'user_id' => null,
                                        'title' => $title,
                                        'description' => $description,
                                        'location_name' => $eventData['lokasi'],
                                        'city' => $eventData['kota'],
                                        'province' => $province?->name,
                                        'event_date' => $eventDate,
                                        'event_end_date' => null,
                                        'event_type' => $eventTypeSlug,
                                        'organizer_name' => $eventData['organizer_name'] ?? null,
                                        'registration_url' => $eventData['registration_url'] ?? null,
                                        'benefits' => $benefits,
                                        'contact_info' => $eventData['kontak_event'] ?? null,
                                        'registration_fees' => $this->normalizeRegistrationFees($eventData['biaya_registrasi'] ?? null),
                                        'social_media' => $this->buildSocialMedia($eventData['kontak_event'] ?? []),
                                        'status' => $data['status'],
                                    ]
                                );
                                
                                // Ensure benefits is updated (force update for JSON fields)
                                if ($benefits !== null) {
                                    $event->benefits = $benefits;
                                    $event->save();
                                }
                                
                                // Handle categories
                                if (!empty($eventData['kategori']) && is_array($eventData['kategori'])) {
                                    $categoryIds = [];
                                    foreach ($eventData['kategori'] as $categoryName) {
                                        $categorySlug = Str::slug($categoryName);
                                        $category = EventCategory::firstOrCreate(
                                            ['slug' => $categorySlug],
                                            ['name' => $categoryName]
                                        );
                                        
                                        if ($category->wasRecentlyCreated) {
                                            $categoriesCreated[] = $categoryName;
                                        }
                                        
                                        $categoryIds[] = $category->id;
                                    }
                                    $event->categories()->sync($categoryIds);
                                }
                                
                                // Handle image upload (if not skipped)
                                if (!$data['skip_images'] && !empty($eventData['image']) && $eventData['image'] !== '-') {
                                    // Check if image file exists in dataevent/images folder
                                    $imagePath = base_path('dataevent/images/' . $eventData['image']);
                                    
                                    if (File::exists($imagePath)) {
                                        try {
                                            $event->addMedia($imagePath)
                                                ->preservingOriginal()
                                                ->toMediaCollection('default');
                                        } catch (\Exception $e) {
                                            // Ignore image errors, continue with import
                                        }
                                    }
                                }
                                
                                DB::commit();
                                $successCount++;
                                
                            } catch (\Exception $e) {
                                DB::rollBack();
                                $errorCount++;
                                \Log::error('Error importing event: ' . $e->getMessage(), [
                                    'event_data' => $eventData ?? null,
                                ]);
                            }
                        }
                        
                        // Cleanup uploaded file
                        Storage::disk($disk)->delete($jsonFile);
                        
                        $message = "Berhasil: {$successCount} event";
                        if ($skippedCount > 0) {
                            $message .= ", Dilewati: {$skippedCount}";
                        }
                        if ($errorCount > 0) {
                            $message .= ", Error: {$errorCount}";
                        }
                        if (!empty($categoriesCreated)) {
                            $uniqueCategories = array_unique($categoriesCreated);
                            $message .= "\nKategori baru: " . implode(', ', $uniqueCategories);
                        }
                        if (!empty($typesCreated)) {
                            $uniqueTypes = array_unique($typesCreated);
                            $message .= "\nTipe baru: " . implode(', ', $uniqueTypes);
                        }
                        
                        Notification::make()
                            ->title('Import Berhasil')
                            ->success()
                            ->body($message)
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->danger()
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
    
    /**
     * Extract province from location string
     */
    protected function extractProvinceFromLocation(string $lokasi, string $kota): ?string
    {
        if (empty($lokasi)) {
            return null;
        }
        
        $parts = array_map('trim', explode(',', $lokasi));
        $parts = array_filter($parts);
        
        if (empty($parts)) {
            return null;
        }
        
        $provinces = [
            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi',
            'Sumatera Selatan', 'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung',
            'Kepulauan Riau', 'DKI Jakarta', 'Jakarta', 'Jawa Barat', 'Jawa Tengah',
            'DI Yogyakarta', 'Yogyakarta', 'Jawa Timur', 'Banten', 'Bali',
            'Nusa Tenggara Barat', 'NTB', 'Nusa Tenggara Timur', 'NTT',
            'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan',
            'Kalimantan Timur', 'Kalimantan Utara', 'Sulawesi Utara',
            'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara',
            'Gorontalo', 'Sulawesi Barat', 'Maluku', 'Maluku Utara',
            'Papua', 'Papua Barat',
        ];
        
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $part = trim($parts[$i]);
            
            if (strcasecmp($part, $kota) === 0) {
                continue;
            }
            
            foreach ($provinces as $province) {
                if (stripos($part, $province) !== false || stripos($province, $part) !== false) {
                    return $province;
                }
            }
        }
        
        return $this->getProvinceFromCity($kota);
    }
    
    /**
     * Get province from city mapping
     */
    protected function getProvinceFromCity(string $city): ?string
    {
        $cityProvinceMap = [
            'Jakarta' => 'DKI Jakarta',
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
        
        if (isset($cityProvinceMap[$city])) {
            return $cityProvinceMap[$city];
        }
        
        foreach ($cityProvinceMap as $mapCity => $province) {
            if (stripos($city, $mapCity) !== false || stripos($mapCity, $city) !== false) {
                return $province;
            }
        }
        
        return null;
    }
    
    /**
     * Normalize event type
     */
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
    
    /**
     * Get or create EventType
     */
    protected function getOrCreateEventType(string $slug): EventType
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
     * Normalize registration fees
     */
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
    
    /**
     * Build social media array from contact info
     */
    protected function buildSocialMedia(array $contactInfo): ?array
    {
        $socialMedia = [];
        
        if (isset($contactInfo['Instagram']) && !empty($contactInfo['Instagram'])) {
            $socialMedia['instagram'] = is_array($contactInfo['Instagram']) 
                ? $contactInfo['Instagram'] 
                : [$contactInfo['Instagram']];
        }
        
        if (isset($contactInfo['Facebook']) && !empty($contactInfo['Facebook'])) {
            $socialMedia['facebook'] = is_array($contactInfo['Facebook']) 
                ? $contactInfo['Facebook'] 
                : [$contactInfo['Facebook']];
        }
        
        if (isset($contactInfo['Twitter']) && !empty($contactInfo['Twitter'])) {
            $socialMedia['twitter'] = is_array($contactInfo['Twitter']) 
                ? $contactInfo['Twitter'] 
                : [$contactInfo['Twitter']];
        }
        
        if (isset($contactInfo['Website']) && !empty($contactInfo['Website'])) {
            $socialMedia['website'] = $contactInfo['Website'];
        }
        
        return empty($socialMedia) ? null : $socialMedia;
    }
}