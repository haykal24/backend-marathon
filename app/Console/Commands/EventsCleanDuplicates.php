<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class EventsCleanDuplicates extends Command
{
    protected $signature = 'events:clean-duplicates
                            {--dry-run : Preview mode, do not delete anything}
                            {--slug : Clean duplicates by slug pattern (e.g., event-2026-1)}
                            {--similarity : Clean duplicates by title + date similarity}
                            {--min-similarity=80 : Minimum similarity percentage for title comparison (0-100)}
                            {--keep=oldest : Which event to keep: oldest (default) or newest}
                            {--force : Force deletion without confirmation}';

    protected $description = 'Clean duplicate events from database based on slug pattern or similarity';

    protected int $deletedCount = 0;
    protected int $skippedCount = 0;
    protected array $duplicates = [];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $bySlug = $this->option('slug');
        $bySimilarity = $this->option('similarity');
        $keep = $this->option('keep'); // 'oldest' or 'newest'
        $force = $this->option('force');

        if (!$bySlug && !$bySimilarity) {
            // Default: clean by slug pattern
            $bySlug = true;
        }

        $this->info('🔍 Scanning for duplicate events...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No events will be deleted');
            $this->newLine();
        }

        if ($bySlug) {
            $this->cleanBySlugPattern($keep, $dryRun, $force);
        }

        if ($bySimilarity) {
            $minSimilarity = (int) $this->option('min-similarity');
            $this->cleanBySimilarity($minSimilarity, $keep, $dryRun, $force);
        }

        // Summary
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Summary:');
        $this->info("   ✓ Deleted: {$this->deletedCount}");
        $this->info("   ⊘ Skipped: {$this->skippedCount}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return Command::SUCCESS;
    }

    /**
     * Clean duplicates by slug pattern (e.g., event-2026-1, event-2026-2)
     */
    protected function cleanBySlugPattern(string $keep, bool $dryRun, bool $force): void
    {
        $this->info('🔎 Method: Slug Pattern Detection');
        $this->info('   Looking for slugs with numeric suffix (e.g., -1, -2)...');
        $this->newLine();

        // Get all events with slug pattern ending in -{number}
        $events = Event::query()
            ->where('slug', 'REGEXP', '^.+-[0-9]+$')
            ->orderBy('slug')
            ->get();

        if ($events->isEmpty()) {
            $this->info('   ✓ No duplicate slugs found by pattern.');
            $this->newLine();
            return;
        }

        $duplicateGroups = [];
        foreach ($events as $event) {
            // Extract base slug (remove -{number} suffix)
            if (preg_match('/^(.+)-(\d+)$/', $event->slug, $matches)) {
                $baseSlug = $matches[1];
                $suffix = (int) $matches[2];

                if (!isset($duplicateGroups[$baseSlug])) {
                    $duplicateGroups[$baseSlug] = [];
                }

                $duplicateGroups[$baseSlug][] = [
                    'event' => $event,
                    'suffix' => $suffix,
                ];
            }
        }

        // Check if original exists (without suffix)
        foreach ($duplicateGroups as $baseSlug => $group) {
            $original = Event::where('slug', $baseSlug)->first();

            if (!$original) {
                // No original found, skip or handle differently
                // Maybe rename the first duplicate to be the original?
                $this->warn("   ⚠️  Base slug '{$baseSlug}' not found, skipping group.");
                continue;
            }

            // Sort by suffix (lower number = earlier duplicate)
            usort($group, fn($a, $b) => $a['suffix'] <=> $b['suffix']);

            // Prepare duplicate events for deletion
            $eventsToDelete = array_map(fn($item) => $item['event'], $group);

            // Decide which to keep
            if ($keep === 'newest') {
                // Keep the newest duplicate, delete original and older duplicates
                $eventsToDelete[] = $original;
                usort($eventsToDelete, fn($a, $b) => $a->created_at <=> $b->created_at);
                $keepEvent = array_pop($eventsToDelete);
            } else {
                // Keep original, delete all duplicates
                $keepEvent = $original;
            }

            $this->processDuplicateGroup($baseSlug, $keepEvent, $eventsToDelete, $dryRun, $force);
        }
    }

    /**
     * Clean duplicates by similarity (title + event_date)
     */
    protected function cleanBySimilarity(int $minSimilarity, string $keep, bool $dryRun, bool $force): void
    {
        $this->info('🔎 Method: Similarity Detection');
        $this->info("   Looking for events with {$minSimilarity}%+ title similarity and same date...");
        $this->newLine();

        $events = Event::query()
            ->orderBy('event_date')
            ->orderBy('title')
            ->get();

        $checked = [];
        $duplicateGroups = [];

        foreach ($events as $event) {
            if (isset($checked[$event->id])) {
                continue;
            }

            $similarEvents = $this->findSimilarEvents($event, $events, $minSimilarity);

            if (count($similarEvents) > 0) {
                $group = [$event, ...$similarEvents];
                $duplicateGroups[] = $group;

                // Mark all as checked
                foreach ($group as $e) {
                    $checked[$e->id] = true;
                }
            }
        }

        foreach ($duplicateGroups as $group) {
            // Sort by created_at
            usort($group, fn($a, $b) => $a->created_at <=> $b->created_at);

            if ($keep === 'newest') {
                $keepEvent = $group[count($group) - 1];
                $eventsToDelete = array_slice($group, 0, -1);
            } else {
                $keepEvent = $group[0];
                $eventsToDelete = array_slice($group, 1);
            }

            $title = $keepEvent->title;
            $this->processDuplicateGroup($title, $keepEvent, $eventsToDelete, $dryRun, $force);
        }

        if (empty($duplicateGroups)) {
            $this->info('   ✓ No similar events found.');
            $this->newLine();
        }
    }

    /**
     * Find similar events based on title similarity and same event_date
     */
    protected function findSimilarEvents(Event $event, $allEvents, int $minSimilarity): array
    {
        $similar = [];

        foreach ($allEvents as $other) {
            if ($other->id === $event->id) {
                continue;
            }

            // Must have same event_date
            if ($other->event_date->format('Y-m-d') !== $event->event_date->format('Y-m-d')) {
                continue;
            }

            // Calculate title similarity
            $similarity = $this->calculateSimilarity(
                mb_strtolower($event->title),
                mb_strtolower($other->title)
            );

            if ($similarity >= $minSimilarity) {
                $similar[] = $other;
            }
        }

        return $similar;
    }

    /**
     * Calculate similarity percentage between two strings
     */
    protected function calculateSimilarity(string $str1, string $str2): int
    {
        similar_text($str1, $str2, $percent);
        return (int) round($percent);
    }

    /**
     * Process a group of duplicate events
     */
    protected function processDuplicateGroup(
        string $identifier,
        Event $keepEvent,
        array $eventsToDelete,
        bool $dryRun,
        bool $force
    ): void {
        if (empty($eventsToDelete)) {
            return;
        }

        $this->info("📋 Group: {$identifier}");
        $this->info("   ✓ Keeping: [{$keepEvent->id}] {$keepEvent->title} ({$keepEvent->slug})");

        foreach ($eventsToDelete as $event) {
            $this->warn("   ✗ Will delete: [{$event->id}] {$event->title} ({$event->slug})");

            if ($dryRun) {
                $this->skippedCount++;
                continue;
            }

            // Confirmation (unless force)
            if (!$force && !$this->confirm("   Delete event [{$event->id}]?", true)) {
                $this->skippedCount++;
                continue;
            }

            try {
                // Delete media files first
                $event->clearMediaCollection('default');

                // Delete event
                $event->delete();

                $this->info("   ✓ Deleted [{$event->id}]");
                $this->deletedCount++;
            } catch (\Exception $e) {
                $this->error("   ✗ Failed to delete [{$event->id}]: {$e->getMessage()}");
                $this->skippedCount++;
            }
        }

        $this->newLine();
    }
}