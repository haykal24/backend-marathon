# Panduan Meletakkan Gambar untuk Seeder

## Lokasi Folder

Gambar untuk seeder harus diletakkan di folder **`backend/public/`**

## Struktur Folder yang Direkomendasikan

```
backend/
└── public/
    ├── seeders/
    │   ├── vendors/          # Logo vendor
    │   │   ├── vendor-1.jpg
    │   │   ├── vendor-2.jpg
    │   │   └── ...
    │   ├── communities/      # Logo komunitas
    │   │   ├── community-1.jpg
    │   │   └── ...
    │   ├── banners/          # Banner iklan
    │   │   ├── banner-homepage-1.jpg
    │   │   ├── banner-homepage-2.jpg
    │   │   ├── banner-sidebar-1.jpg
    │   │   └── ...
    │   ├── site-settings/    # Logo website, dll
    │   │   ├── logo.png
    │   │   ├── logo-footer.png
    │   │   └── ...
    │   └── event-types/      # Thumbnail event type
    │       ├── road-run.jpg
    │       ├── trail-run.jpg
    │       └── ...
    └── placeholder.jpg       # Placeholder default (wajib)
```

## Format Gambar

- **Format yang didukung:** JPG, PNG, WEBP
- **Ukuran disarankan:**
  - Logo: 200x200px - 500x500px
  - Banner: 1200x400px - 1920x600px
  - Thumbnail: 400x300px - 800x600px

## Cara Menggunakan di Seeder

Seeder akan otomatis:
1. Mencari gambar di folder `public/seeders/`
2. Jika tidak ditemukan, menggunakan `placeholder.jpg` sebagai fallback
3. Meng-upload gambar ke Spatie Media Library
4. Otomatis generate format WebP

## Contoh Penggunaan

### Vendor Seeder
```php
$imagePath = public_path('seeders/vendors/vendor-1.jpg');
if (File::exists($imagePath)) {
    $vendor->addMedia($imagePath)
        ->usingName('Logo ' . $vendor->name)
        ->toMediaCollection('default');
}
```

### Banner Seeder
```php
$bannerPath = public_path('seeders/banners/banner-homepage-1.jpg');
if (File::exists($bannerPath)) {
    $banner->addMedia($bannerPath)
        ->usingName($banner->name)
        ->toMediaCollection('default');
}
```

## Catatan Penting

1. **Placeholder wajib:** Pastikan file `placeholder.jpg` ada di `backend/public/`
2. **Naming convention:** Gunakan nama file yang deskriptif (contoh: `vendor-medali-1.jpg`)
3. **Ukuran file:** Optimalkan gambar sebelum upload (max 2MB per file)
4. **WebP otomatis:** Spatie Media Library akan otomatis generate WebP saat upload

