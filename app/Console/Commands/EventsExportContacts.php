<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class EventsExportContacts extends Command
{
    /**
     * Signature:
     *  php artisan events:export-contacts
     *      --include=phone,email    (default: phone,email)
     *      --exclude-phone=*        (bisa diulang, contoh: --exclude-phone=081234567890)
     *      --exclude-email=*        (bisa diulang)
     *      --path=storage/app/...   (opsional, default: storage/app/event-contacts.csv)
     */
    protected $signature = 'events:export-contacts
                            {--include=phone,email : Comma separated list: phone,email}
                            {--exclude-phone=* : Phone numbers to exclude (can be repeated)}
                            {--exclude-email=* : Emails to exclude (can be repeated)}
                            {--path= : Output CSV path (default: storage/app/event-contacts.csv)}';

    protected $description = 'Export kontak penyelenggara event (phone/email) ke CSV yang siap dibuka di Excel';

    public function handle(): int
    {
        $include = $this->parseIncludeOption((string) $this->option('include'));
        $excludePhones = (array) $this->option('exclude-phone');
        $excludeEmails = (array) $this->option('exclude-email');

        if (empty($include)) {
            $this->error('Option --include harus berisi minimal salah satu dari: phone,email');
            return Command::INVALID;
        }

        // Normalisasi kontak yang dikecualikan
        $normalizedExcludePhones = $this->normalizePhones($excludePhones);
        $normalizedExcludeEmails = $this->normalizeEmails($excludeEmails);

        $outputPath = $this->option('path')
            ? (string) $this->option('path')
            : storage_path('app/event-contacts.csv');

        // Konfirmasi jika file sudah ada
        if (file_exists($outputPath) && !$this->confirm("File {$outputPath} sudah ada. Overwrite?", false)) {
            $this->warn('Dibatalkan oleh user.');
            return Command::INVALID;
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($outputPath, 'w');
        if ($handle === false) {
            $this->error("Tidak bisa menulis ke file: {$outputPath}");
            return Command::FAILURE;
        }

        // Header CSV
        fputcsv($handle, [
            'event_id',
            'event_title',
            'event_date',
            'city',
            'province',
            'contact_type',
            'contact_label',
            'contact_value',
            'source', // contact_info | user_phone
        ]);

        $exportedCount = 0;

        $this->info('🔍 Mengekspor kontak dari events.contact_info ...');
        $this->newLine();

        Event::query()
            ->whereNotNull('contact_info')
            ->orderBy('id')
            ->chunkById(200, function ($events) use (
                $handle,
                $include,
                $normalizedExcludePhones,
                $normalizedExcludeEmails,
                &$exportedCount
            ) {
                foreach ($events as $event) {
                    $contacts = (array) $event->contact_info;

                    foreach ($contacts as $entry) {
                        [$type, $label, $value] = $this->extractContact($entry);

                        if (!$value) {
                            continue;
                        }

                        // Filter berdasarkan tipe yang di-include
                        if ($type === 'phone' && !in_array('phone', $include, true)) {
                            continue;
                        }

                        if ($type === 'email' && !in_array('email', $include, true)) {
                            continue;
                        }

                        // Normalisasi dan cek exclude list
                        $normalizedPhone = $this->normalizePhone($value);
                        $normalizedEmail = $this->normalizeEmail($value);

                        if ($type === 'phone' && $normalizedPhone && in_array($normalizedPhone, $normalizedExcludePhones, true)) {
                            continue;
                        }

                        if ($type === 'email' && $normalizedEmail && in_array($normalizedEmail, $normalizedExcludeEmails, true)) {
                            continue;
                        }

                        fputcsv($handle, [
                            $event->id,
                            $event->title,
                            optional($event->event_date)->format('Y-m-d'),
                            $event->city,
                            $event->province,
                            $type,
                            $label,
                            $value,
                            'contact_info',
                        ]);

                        $exportedCount++;
                    }
                }
            });

        fclose($handle);

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Total kontak diexport : {$exportedCount}");
        $this->info("📁 File                    : {$outputPath}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->newLine();
        $this->warn('File CSV ini bisa langsung dibuka di Excel / Google Sheets.');

        return Command::SUCCESS;
    }

    /**
     * Parse nilai --include menjadi array tipe yang valid.
     */
    protected function parseIncludeOption(string $value): array
    {
        $parts = array_filter(array_map('trim', explode(',', $value)));

        $valid = ['phone', 'email'];

        return array_values(array_intersect($valid, $parts));
    }

    /**
     * Ekstrak contact_type, label, dan value dari entry contact_info.
     *
     * @return array{0:string,1:string,2:string}
     */
    protected function extractContact(mixed $entry): array
    {
        $label = '';
        $value = '';
        $type = 'other';

        if (is_array($entry)) {
            $label = (string) ($entry['label'] ?? $entry['type'] ?? '');
            $value = (string) ($entry['value'] ?? $entry['contact'] ?? $entry['phone'] ?? $entry['email'] ?? '');

            // Coba deteksi tipe dari key
            if (!empty($entry['phone']) || Str::contains(Str::lower($label), ['wa', 'whatsapp', 'phone', 'telp', 'telepon'])) {
                $type = 'phone';
            } elseif (!empty($entry['email']) || Str::contains(Str::lower($label), ['email'])) {
                $type = 'email';
            }
        } elseif (is_string($entry)) {
            $value = $entry;
        } else {
            return ['other', '', ''];
        }

        // Jika tipe belum jelas, deteksi dari pola value
        if ($type === 'other') {
            if ($this->looksLikeEmail($value)) {
                $type = 'email';
            } elseif ($this->looksLikePhone($value)) {
                $type = 'phone';
            }
        }

        return [$type, $label, $value];
    }

    protected function looksLikeEmail(string $value): bool
    {
        return (bool) filter_var($this->normalizeEmail($value), FILTER_VALIDATE_EMAIL);
    }

    protected function looksLikePhone(string $value): bool
    {
        $digits = $this->normalizePhone($value);

        return strlen($digits) >= 8;
    }

    /**
     * Normalisasi satu nomor HP → hanya angka.
     */
    protected function normalizePhone(?string $value): string
    {
        if (!$value) {
            return '';
        }

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * Normalisasi banyak nomor HP.
     */
    protected function normalizePhones(array $phones): array
    {
        $normalized = [];

        foreach ($phones as $phone) {
            $v = $this->normalizePhone($phone);
            if ($v !== '') {
                $normalized[] = $v;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function normalizeEmail(?string $value): string
    {
        if (!$value) {
            return '';
        }

        return Str::lower(trim($value));
    }

    protected function normalizeEmails(array $emails): array
    {
        $normalized = [];

        foreach ($emails as $email) {
            $v = $this->normalizeEmail($email);
            if ($v !== '') {
                $normalized[] = $v;
            }
        }

        return array_values(array_unique($normalized));
    }
}