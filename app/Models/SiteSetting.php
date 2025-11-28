<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const KEY_DEFINITIONS = [
        // General
        'site_name' => [
            'label' => 'Nama Website',
            'type' => 'text',
            'group' => 'general',
            'description' => 'Nama website yang ditampilkan di header & metadata.',
            'default' => 'Marathon Indonesia',
        ],
        'site_description' => [
            'label' => 'Deskripsi Website',
            'type' => 'textarea',
            'group' => 'general',
            'description' => 'Deskripsi singkat platform untuk keperluan meta & tampilan.',
            'default' => 'Platform digital #1 di Indonesia sebagai pusat informasi dan komunitas event lari.',
        ],

        // Appearance
        'logo' => [
            'label' => 'Logo Header',
            'type' => 'image',
            'group' => 'appearance',
            'description' => 'Logo utama yang tampil di header.',
            'seed' => 'logo.png',
        ],
        'logo_footer' => [
            'label' => 'Logo Footer',
            'type' => 'image',
            'group' => 'appearance',
            'description' => 'Logo yang tampil di bagian footer.',
            'seed' => 'logo.png',
        ],
        'homepage_cta_background' => [
            'label' => 'Background CTA Homepage (Desktop)',
            'type' => 'image',
            'group' => 'homepage',
            'description' => 'Gambar latar untuk section CTA di homepage (versi desktop).',
        ],
        'homepage_cta_background_mobile' => [
            'label' => 'Background CTA Homepage (Mobile)',
            'type' => 'image',
            'group' => 'homepage',
            'description' => 'Gambar latar untuk section CTA di homepage (versi mobile).',
        ],

        // Page Headers
        'header_bg_events' => [
            'label' => 'Header Background Halaman Event',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar belakang untuk header di halaman daftar event.',
        ],
        'header_bg_blog' => [
            'label' => 'Header Background Halaman Blog',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar belakang untuk header di halaman daftar artikel blog.',
        ],
        'header_bg_ekosistem' => [
            'label' => 'Header Background Halaman Induk Ekosistem',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar belakang untuk header di halaman utama Ekosistem.',
        ],
        'header_bg_komunitas' => [
            'label' => 'Header Background Halaman Komunitas Lari',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman direktori Komunitas Lari.',
        ],
        'header_bg_race_management' => [
            'label' => 'Header Background Halaman Race Management',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman direktori Race Management.',
        ],
        'header_bg_vendor_medali' => [
            'label' => 'Header Background Halaman Vendor Medali',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman direktori Vendor Medali.',
        ],
        'header_bg_vendor_jersey' => [
            'label' => 'Header Background Halaman Vendor Jersey',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman direktori Vendor Jersey.',
        ],
        'header_bg_vendor_fotografer' => [
            'label' => 'Header Background Halaman Fotografer Lari',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman direktori Fotografer Lari.',
        ],
        'header_bg_faq' => [
            'label' => 'Header Background Halaman FAQ',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman FAQ.',
        ],
        'header_bg_rate_card' => [
            'label' => 'Header Background Halaman Rate Card',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman Rate Card.',
        ],
        'header_bg_static' => [
            'label' => 'Header Background Halaman Statis (Default)',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar default untuk halaman statis (privacy, tentang, kontak).',
        ],
        'header_bg_privacy' => [
            'label' => 'Header Background Halaman Kebijakan Privasi',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman kebijakan privasi.',
        ],
        'header_bg_about' => [
            'label' => 'Header Background Halaman Tentang Kami',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman tentang kami.',
        ],
        'header_bg_contact' => [
            'label' => 'Header Background Halaman Kontak',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman kontak.',
        ],
        'auth_image' => [
            'label' => 'Auth Background Image (Login/Register)',
            'type' => 'image',
            'group' => 'authentication',
            'description' => 'Gambar latar untuk halaman login/register (kolom kiri). Prioritas tertinggi.',
        ],
        'auth_login_image' => [
            'label' => 'Auth Login Image (Fallback)',
            'type' => 'image',
            'group' => 'authentication',
            'description' => 'Gambar fallback khusus untuk halaman login jika auth_image tidak ada.',
        ],
        'header_bg_dashboard' => [
            'label' => 'Header Background Halaman Dashboard Mitra',
            'type' => 'image',
            'group' => 'page_headers',
            'description' => 'Gambar latar untuk header halaman Dashboard Mitra.',
        ],

        // Contact
        'contact_email' => [
            'label' => 'Email Kontak',
            'type' => 'email',
            'group' => 'contact',
            'description' => 'Alamat email resmi untuk menerima pesan masuk.',
            'default' => 'help@marathon.id',
        ],
        'contact_phone' => [
            'label' => 'Telepon Kontak',
            'type' => 'phone',
            'group' => 'contact',
            'description' => 'Nomor telepon yang bisa dihubungi.',
        ],
        'contact_whatsapp' => [
            'label' => 'WhatsApp Kontak',
            'type' => 'phone',
            'group' => 'contact',
            'description' => 'Nomor WhatsApp untuk komunikasi cepat.',
        ],
        'address' => [
            'label' => 'Alamat Kantor',
            'type' => 'textarea',
            'group' => 'contact',
            'description' => 'Alamat lengkap penyelenggara.',
        ],
        'contact_hours_primary' => [
            'label' => 'Jam Operasional Utama',
            'type' => 'text',
            'group' => 'contact',
            'description' => 'Contoh: Senin–Jumat 09.00–17.00 WIB',
            'default' => 'Senin–Jumat 09.00–17.00 WIB',
        ],
        'contact_hours_primary_note' => [
            'label' => 'Catatan Jam Operasional Utama',
            'type' => 'textarea',
            'group' => 'contact',
            'description' => 'Penjelasan singkat mengenai respon selama jam utama.',
            'default' => 'Balasan tercepat via WhatsApp & email. Di luar jam kerja silakan tinggalkan pesan, tim kami akan follow-up maksimal keesokan harinya.',
        ],
        'contact_hours_secondary' => [
            'label' => 'Jam Operasional Sekunder',
            'type' => 'text',
            'group' => 'contact',
            'description' => 'Contoh: Sabtu 09.00–13.00 WIB',
            'default' => 'Sabtu 09.00–13.00 WIB',
        ],
        'contact_hours_secondary_note' => [
            'label' => 'Catatan Jam Operasional Sekunder',
            'type' => 'textarea',
            'group' => 'contact',
            'description' => 'Penjelasan tambahan untuk jam operasional sekunder.',
            'default' => 'Tim siaga untuk koreksi data urgent & admin event.',
        ],

        // Social Media
        'instagram_handle' => [
            'label' => 'Instagram Handle',
            'type' => 'text',
            'group' => 'social',
            'description' => 'Handle Instagram (contoh: @marathonindonesia).',
        ],
        'facebook_url' => [
            'label' => 'Facebook URL',
            'type' => 'url',
            'group' => 'social',
            'description' => 'URL halaman Facebook resmi.',
        ],
        'twitter_handle' => [
            'label' => 'Twitter/X Handle',
            'type' => 'text',
            'group' => 'social',
            'description' => 'Handle Twitter/X (tanpa URL).',
        ],

        // Footer
        'footer_description' => [
            'label' => 'Deskripsi Footer',
            'type' => 'textarea',
            'group' => 'footer',
            'description' => 'Deskripsi singkat yang tampil di footer.',
            'default' => 'Platform digital #1 di Indonesia sebagai pusat informasi dan komunitas event lari.',
        ],
        'footer_copyright' => [
            'label' => 'Copyright Footer',
            'type' => 'text',
            'group' => 'footer',
            'description' => 'Teks copyright yang tampil di footer.',
            'default' => '© 2025 Marathon Indonesia. All rights reserved.',
        ],
    ];

    protected $appends = [
        'key_label',
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'r2'))
            ->useFallbackUrl('/images/placeholder.jpg');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->width(1920)
            ->sharpen(10)
            ->nonQueued()
            ->performOnCollections('default');
    }

    public function getImageValue(): ?string
    {
        if ($this->type === 'image') {
            $media = $this->getFirstMedia('default');
            return $media ? $media->getUrl('webp') : null;
        }
        return $this->value;
    }

    public function getKeyLabelAttribute(): string
    {
        return static::KEY_DEFINITIONS[$this->key]['label'] ?? $this->key;
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'image') {
            return $setting->getImageValue();
        }

        if ($setting->type === 'json') {
            return json_decode($setting->value, true);
        }

        return $setting->value ?? $default;
    }

    public static function set(string $key, $value, string $type = 'text', string $group = 'general', ?string $description = null): self
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->type = $type;
        $setting->group = $group;
        $setting->description = $description;
        $setting->save();

        return $setting;
    }

    public static function keyOptions(): array
    {
        return collect(static::KEY_DEFINITIONS)
            ->mapWithKeys(fn ($definition, $key) => [$key => $definition['label'] ?? $key])
            ->toArray();
    }

    public static function availableKeyOptions(?string $currentKey = null): array
    {
        $options = static::keyOptions();
        $usedKeys = static::pluck('key')->toArray();

        if ($currentKey) {
            $usedKeys = array_diff($usedKeys, [$currentKey]);
        }

        foreach ($usedKeys as $usedKey) {
            unset($options[$usedKey]);
        }

        return $options;
    }

    public static function groupOptions(): array
    {
        return collect(static::KEY_DEFINITIONS)
            ->pluck('group')
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($group) => [$group => static::groupLabel($group)])
            ->toArray();
    }

    public static function getDefinition(string $key): array
    {
        return static::KEY_DEFINITIONS[$key] ?? [];
    }

    public static function groupLabel(string $group): string
    {
        return match ($group) {
            'social' => 'Social Media',
            'homepage' => 'Homepage',
            'page_headers' => 'Page Headers',
            default => ucfirst(str_replace('_', ' ', $group)),
        };
    }
}