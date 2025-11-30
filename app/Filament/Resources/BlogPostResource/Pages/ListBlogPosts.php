<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Helpers\ContentCleaner;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Tags\Tag;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

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
                        ->label('File JSON')
                        ->required()
                        ->acceptedFileTypes(['application/json', 'text/json'])
                        ->maxSize(10240) // 10MB
                        ->disk(config('filesystems.default', 'r2'))
                        ->directory('imports')
                        ->visibility('private')
                        ->helperText('Upload file JSON berisi array artikel. Format: [{"title": "...", "content": "...", "category": "...", ...}, ...]. Support banyak artikel sekaligus.'),
                    Components\Select::make('default_author_id')
                        ->label('Author Default')
                        ->options(BlogAuthor::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->default(fn () => BlogAuthor::first()?->id)
                        ->helperText('Author yang akan digunakan jika tidak ada di JSON'),
                    Components\Select::make('default_category_id')
                        ->label('Kategori Default')
                        ->options(BlogCategory::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->default(fn () => BlogCategory::first()?->id)
                        ->helperText('Kategori yang akan digunakan jika tidak ada di JSON'),
                    Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->default('draft')
                        ->required()
                        ->helperText('Status default untuk post yang di-import'),
                    Components\Toggle::make('skip_duplicates')
                        ->label('Skip Duplikat')
                        ->default(true)
                        ->helperText('Skip post yang sudah ada (berdasarkan slug)'),
                ])
                ->action(function (array $data) {
                    try {
                        $jsonFile = $data['json_file'];
                        
                        // Handle path dari Filament FileUpload
                        if (is_array($jsonFile)) {
                            $jsonFile = $jsonFile[0] ?? null;
                        }
                        
                        if (!$jsonFile) {
                            throw new \Exception('File JSON tidak ditemukan');
                        }
                        
                        // Baca file dari storage (R2)
                        $disk = config('filesystems.default', 'r2');
                        $jsonContent = Storage::disk($disk)->get($jsonFile);
                        
                        if (!$jsonContent) {
                            throw new \Exception('File JSON tidak dapat dibaca dari disk: ' . $disk);
                        }
                        $posts = json_decode($jsonContent, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('Format JSON tidak valid: ' . json_last_error_msg());
                        }
                        
                        if (!is_array($posts)) {
                            throw new \Exception('JSON harus berupa array of objects');
                        }
                        
                        $totalPosts = count($posts);
                        if ($totalPosts === 0) {
                            throw new \Exception('Tidak ada artikel ditemukan di JSON');
                        }
                        
                        $successCount = 0;
                        $skippedCount = 0;
                        $errorCount = 0;
                        $categoriesCreated = [];
                        
                        // Progress notification
                        Notification::make()
                            ->title('Memulai Import')
                            ->body("Memproses {$totalPosts} artikel...")
                            ->info()
                            ->send();
                        
                        foreach ($posts as $index => $postData) {
                            try {
                                // Get title (support multiple field names)
                                $title = $postData['title'] ?? $postData['judul'] ?? 'Untitled';
                                
                                // Generate slug dari title atau gunakan slug yang ada
                                $slug = !empty($postData['slug']) 
                                    ? Str::slug($postData['slug']) 
                                    : Str::slug($title);
                                
                                // Check duplicate jika skip_duplicates aktif
                                if ($data['skip_duplicates'] && BlogPost::where('slug', $slug)->exists()) {
                                    $skippedCount++;
                                    continue;
                                }
                                
                                // Handle author (support: author, author_name, penulis)
                                $authorId = $data['default_author_id'];
                                $authorName = $postData['author'] ?? $postData['author_name'] ?? $postData['penulis'] ?? null;
                                
                                if ($authorName) {
                                    $authorEmail = $postData['author_email'] ?? Str::slug($authorName) . '@marathonindonesia.com';
                                    
                                    $author = BlogAuthor::firstOrCreate(
                                        ['email' => $authorEmail],
                                        ['name' => $authorName]
                                    );
                                    $authorId = $author->id;
                                }
                                
                                // Handle category (support: category, category_name, categories, kategori)
                                $categoryId = $data['default_category_id'];
                                $categoryName = null;
                                
                                if (isset($postData['categories']) && is_array($postData['categories']) && !empty($postData['categories'])) {
                                    $categoryName = $postData['categories'][0];
                                } elseif (isset($postData['category']) && !empty($postData['category'])) {
                                    $categoryName = is_array($postData['category']) ? ($postData['category'][0] ?? null) : $postData['category'];
                                } elseif (isset($postData['category_name']) && !empty($postData['category_name'])) {
                                    $categoryName = $postData['category_name'];
                                } elseif (isset($postData['kategori']) && !empty($postData['kategori'])) {
                                    $categoryName = $postData['kategori'];
                                }
                                
                                if ($categoryName) {
                                    $categorySlug = Str::slug($categoryName);
                                    
                                    $category = BlogCategory::firstOrCreate(
                                        ['slug' => $categorySlug],
                                        ['name' => $categoryName, 'is_visible' => true]
                                    );
                                    
                                    if ($category->wasRecentlyCreated) {
                                        $categoriesCreated[] = $categoryName;
                                    }
                                    
                                    $categoryId = $category->id;
                                }
                                
                                // Parse published_at (support: published_at, published_date, tanggal_diterbitkan)
                                $publishedAt = null;
                                if (isset($postData['published_at']) && !empty($postData['published_at'])) {
                                    $publishedAt = \Carbon\Carbon::parse($postData['published_at']);
                                } elseif (isset($postData['published_date']) && !empty($postData['published_date'])) {
                                    $publishedAt = \Carbon\Carbon::parse($postData['published_date']);
                                } elseif (isset($postData['tanggal_diterbitkan']) && !empty($postData['tanggal_diterbitkan'])) {
                                    $publishedAt = \Carbon\Carbon::parse($postData['tanggal_diterbitkan']);
                                } else {
                                    $publishedAt = now();
                                }
                                
                                // Get content (support: content, konten, isi, body)
                                $rawContent = $postData['content'] ?? $postData['konten'] ?? $postData['isi'] ?? $postData['body'] ?? '';
                                
                                // Clean and format content
                                $content = ContentCleaner::clean($rawContent);
                                
                                // Get excerpt (support: excerpt, summary, kutipan)
                                $rawExcerpt = $postData['excerpt'] ?? $postData['summary'] ?? $postData['kutipan'] ?? null;
                                $excerpt = $rawExcerpt ? ContentCleaner::clean($rawExcerpt, true) : Str::limit(strip_tags($content), 200);
                                
                                // Get SEO fields (support: seo_title, meta_title, seo_description, meta_description)
                                $seoTitle = $postData['seo_title'] ?? $postData['meta_title'] ?? null;
                                $seoDescription = $postData['seo_description'] ?? $postData['meta_description'] ?? null;
                                
                                // Create post
                                $post = BlogPost::updateOrCreate(
                                    ['slug' => $slug],
                                    [
                                        'blog_author_id' => $authorId,
                                        'blog_category_id' => $categoryId,
                                        'title' => $title,
                                        'slug' => $slug,
                                        'excerpt' => $excerpt,
                                        'content' => $content,
                                        'seo_title' => $seoTitle,
                                        'seo_description' => $seoDescription,
                                        'status' => $data['status'],
                                        'published_at' => $publishedAt,
                                        'is_for_maraton_id' => null, // Default: both portals
                                    ]
                                );
                                
                                // Handle tags (support: tags, tag)
                                $tags = $postData['tags'] ?? $postData['tag'] ?? [];
                                if (!empty($tags) && is_array($tags)) {
                                    $tagModels = [];
                                    foreach ($tags as $tagName) {
                                        if (empty($tagName) || !is_string($tagName)) continue;
                                        
                                        $tag = Tag::firstOrCreate(
                                            ['slug' => Str::slug($tagName), 'type' => 'blog'],
                                            ['name' => trim($tagName)]
                                        );
                                        $tagModels[] = $tag;
                                    }
                                    if (!empty($tagModels)) {
                                        $post->syncTags($tagModels);
                                    }
                                }
                                
                                // Handle banner image (support: banner, image, thumbnail, gambar)
                                // Note: Untuk upload image dari path, perlu logic tambahan
                                // Bisa ditambahkan nanti jika diperlukan
                                
                                $successCount++;
                            } catch (\Exception $e) {
                                $errorCount++;
                                \Log::error('Error importing post: ' . $e->getMessage(), [
                                    'post_data' => $postData,
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        }
                        
                        // Cleanup uploaded file
                        $disk = config('filesystems.default', 'r2');
                        Storage::disk($disk)->delete($jsonFile);
                        
                        $message = "Berhasil: {$successCount} artikel";
                        if ($skippedCount > 0) {
                            $message .= ", Dilewati: {$skippedCount}";
                        }
                        if ($errorCount > 0) {
                            $message .= ", Error: {$errorCount}";
                        }
                        if (!empty($categoriesCreated)) {
                            $uniqueCategories = array_unique($categoriesCreated);
                            $message .= "\nKategori baru dibuat: " . implode(', ', $uniqueCategories);
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
    
}