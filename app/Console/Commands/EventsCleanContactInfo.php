<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class EventsCleanContactInfo extends Command
{
    protected $signature = 'events:clean-contact-info
                            {--dry-run : Preview mode, do not persist changes}
                            {--phone=* : Additional phone numbers to scrub}
                            {--email=* : Additional email addresses to scrub}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Remove contact info entries that match unwanted phone numbers or emails';

    protected int $affectedEvents = 0;
    protected int $removedContacts = 0;

    protected array $defaultPhones = [
        '082337867209',
        '0823 3786 7209',
    ];

    protected array $defaultEmails = [
        'info@jadwallari.id',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $phones = $this->preparePhones();
        $emails = $this->prepareEmails();

        if (empty($phones) && empty($emails)) {
            $this->warn('No phone numbers or emails provided to clean.');
            return Command::INVALID;
        }

        $this->info('🔍 Scanning events for unwanted contact info...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - Changes will NOT be saved.');
            $this->newLine();
        }

        Event::query()
            ->whereNotNull('contact_info')
            ->chunkById(200, function ($events) use ($phones, $emails, $dryRun, $force) {
                foreach ($events as $event) {
                    $contacts = $event->contact_info;
                    if (empty($contacts) || !is_iterable($contacts)) {
                        continue;
                    }

                    [$cleaned, $removed] = $this->filterContacts($contacts, $phones, $emails);

                    if ($removed === 0) {
                        continue;
                    }

                    $this->affectedEvents++;
                    $this->removedContacts += $removed;

                    $this->info("Event #{$event->id} ({$event->title}) – removed {$removed} contact entries.");

                    if ($dryRun) {
                        continue;
                    }

                    if (!$force && !$this->confirm('   Save changes for this event?', true)) {
                        $this->warn('   ↳ Skipped by user.');
                        continue;
                    }

                    $event->contact_info = array_values($cleaned);
                    $event->save();
                    $this->comment('   ✓ Changes saved.');
                }
            });

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Summary');
        $this->info("   ✓ Events updated : {$this->affectedEvents}");
        $this->info("   ✂ Contacts removed : {$this->removedContacts}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return Command::SUCCESS;
    }

    protected function preparePhones(): array
    {
        $phones = array_merge($this->defaultPhones, (array) $this->option('phone'));

        $normalized = [];
        foreach ($phones as $phone) {
            $value = $this->normalizePhone($phone);
            if (!empty($value)) {
                $normalized[] = $value;
            }
        }

        return array_unique($normalized);
    }

    protected function prepareEmails(): array
    {
        $emails = array_merge($this->defaultEmails, (array) $this->option('email'));

        $normalized = [];
        foreach ($emails as $email) {
            $value = strtolower(trim($email));
            if (!empty($value)) {
                $normalized[] = $value;
            }
        }

        return array_unique($normalized);
    }

    protected function filterContacts(iterable $contacts, array $phones, array $emails): array
    {
        $cleaned = [];
        $removed = 0;

        foreach ($contacts as $contact) {
            if (!$this->shouldRemoveContact($contact, $phones, $emails)) {
                $cleaned[] = $contact;
            } else {
                $removed++;
            }
        }

        return [$cleaned, $removed];
    }

    protected function shouldRemoveContact($contact, array $phones, array $emails): bool
    {
        $candidates = [];

        if (is_array($contact)) {
            foreach (['value', 'label', 'contact', 'phone', 'email'] as $key) {
                if (!empty($contact[$key]) && is_string($contact[$key])) {
                    $candidates[] = $contact[$key];
                }
            }
        } elseif (is_string($contact)) {
            $candidates[] = $contact;
        }

        foreach ($candidates as $candidate) {
            $normalizedPhone = $this->normalizePhone($candidate);
            if ($normalizedPhone && in_array($normalizedPhone, $phones, true)) {
                return true;
            }

            $normalizedEmail = strtolower(trim($candidate));
            if ($normalizedEmail && in_array($normalizedEmail, $emails, true)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizePhone(?string $value): string
    {
        if (!$value) {
            return '';
        }

        return preg_replace('/\D+/', '', $value) ?? '';
    }
}

