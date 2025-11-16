<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;

echo "=== EVENT COUNT ===" . PHP_EOL;
echo "Total events: " . Event::count() . PHP_EOL;
echo "Published events: " . Event::where('status', 'published')->count() . PHP_EOL;
echo "Draft events: " . Event::where('status', 'draft')->count() . PHP_EOL;
echo "Pending review: " . Event::where('status', 'pending_review')->count() . PHP_EOL;
echo "Featured hero: " . Event::where('is_featured_hero', true)->count() . PHP_EOL;

echo "\n=== EVENT DATE RANGE ===" . PHP_EOL;
$dates = Event::where('status', 'published')->selectRaw('MIN(event_date) as min_date, MAX(event_date) as max_date')->first();
echo "Published event dates: " . $dates->min_date . " to " . $dates->max_date . PHP_EOL;

echo "\n=== PAGINATION TEST ===" . PHP_EOL;
$perPage = 12;
$total = Event::where('status', 'published')->count();
$lastPage = ceil($total / $perPage);
echo "Total published: $total" . PHP_EOL;
echo "Per page: $perPage" . PHP_EOL;
echo "Last page: $lastPage" . PHP_EOL;
echo "Page 1 will show: " . min($perPage, $total) . " events" . PHP_EOL;
?>

