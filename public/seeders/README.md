# Folder Gambar untuk Seeder

Letakkan gambar-gambar untuk seeder di folder ini sesuai dengan struktur berikut:

## Struktur Folder

```
seeders/
├── vendors/              # Logo vendor (untuk VendorSeeder)
│   ├── medali-runner-indonesia.jpg
│   ├── race-management-pro.jpg
│   └── ...
├── communities/          # Logo komunitas (untuk RunningCommunitySeeder)
│   ├── jakarta-runners-club.jpg
│   ├── bandung-running-community.jpg
│   └── ...
├── banners/             # Banner iklan (untuk AdBannerSeeder)
│   ├── homepage_slider-1.jpg
│   ├── banner_main-1.jpg
│   ├── sidebar_1-1.jpg
│   ├── sidebar_2-1.jpg
│   └── ...
├── site-settings/       # Logo website (untuk SiteSettingSeeder)
│   ├── logo.png (atau .jpg)
│   └── logo-footer.png (atau .jpg)
└── event-types/         # Thumbnail event type (untuk EventTypeSeeder - jika ada)
    ├── road-run.jpg
    ├── trail-run.jpg
    └── ...
```

## Naming Convention

### Vendors
- **Berdasarkan slug nama:** `medali-runner-indonesia.jpg`
- **Atau berdasarkan index:** `vendor-1.jpg`, `vendor-2.jpg`, dll

### Communities
- **Berdasarkan slug nama:** `jakarta-runners-club.jpg`
- **Atau berdasarkan index:** `community-1.jpg`, `community-2.jpg`, dll

### Banners
- **Berdasarkan slot location:** `homepage_slider-1.jpg`, `banner_main-1.jpg`, `sidebar_1-1.jpg`, `sidebar_2-1.jpg`
- **Atau berdasarkan index:** `banner-1.jpg`, `banner-2.jpg`, dll

### Site Settings
- **Logo header:** `logo.png` atau `logo.jpg`
- **Logo footer:** `logo-footer.png` atau `logo-footer.jpg`

## Format Gambar

- **Format:** JPG, PNG, WEBP
- **Ukuran disarankan:**
  - Logo: 200x200px - 500x500px
  - Banner: 1200x400px - 1920x600px
  - Thumbnail: 400x300px - 800x600px
- **Ukuran file:** Max 2MB per file

## Placeholder

File `placeholder.jpg` harus ada di `backend/public/` sebagai fallback jika gambar spesifik tidak ditemukan.

## Cara Kerja Seeder

1. Seeder akan mencari gambar spesifik berdasarkan nama/slug
2. Jika tidak ditemukan, akan mencari berdasarkan index (vendor-1.jpg, dll)
3. Jika masih tidak ditemukan, akan menggunakan `placeholder.jpg`
4. Gambar akan di-upload ke Spatie Media Library
5. Format WebP akan otomatis di-generate

