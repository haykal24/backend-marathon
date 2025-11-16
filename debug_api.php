<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;
use App\Http\Resources\Api\V1\EventResource;

// Simulate API query
$query = Event::with(['categories'])->where('status', 'published');
$sort = 'latest';
$query->orderBy('created_at', 'desc');
$perPage = 12;
$events = $query->paginate($perPage);

echo "=== API RESPONSE SIMULATION ===" . PHP_EOL;
echo "Current page: " . $events->currentPage() . PHP_EOL;
echo "Last page: " . $events->lastPage() . PHP_EOL;
echo "Per page: " . $events->perPage() . PHP_EOL;
echo "Total: " . $events->total() . PHP_EOL;
echo "Count on page: " . count($events->items()) . PHP_EOL;
echo "Has more pages: " . ($events->hasMorePages() ? 'YES' : 'NO') . PHP_EOL;

echo "\n=== SAMPLE EVENT DATA ===" . PHP_EOL;
$sample = $events->items()[0] ?? null;
if ($sample) {
    $resource = new EventResource($sample);
    echo "Event title: " . $sample->title . PHP_EOL;
    echo "Status: " . $sample->status . PHP_EOL;
    echo "Is featured hero: " . ($sample->is_featured_hero ? 'YES' : 'NO') . PHP_EOL;
}
?>

