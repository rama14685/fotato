<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Photo;
use App\Jobs\ProcessPhotoFaceDetection;

echo "=== Dispatching Face Detection Jobs ===\n\n";

$photos = Photo::whereDoesntHave('faceEmbedding')->get();
$total = $photos->count();

echo "Found {$total} photos without face embeddings.\n";
echo "Dispatching jobs...\n\n";

$dispatched = 0;
foreach ($photos as $photo) {
    try {
        ProcessPhotoFaceDetection::dispatch($photo);
        $dispatched++;
        
        if ($dispatched % 100 == 0) {
            echo "Dispatched {$dispatched}/{$total} jobs...\n";
        }
    } catch (\Exception $e) {
        echo "Error dispatching job for photo {$photo->id}: {$e->getMessage()}\n";
    }
}

echo "\n✓ Successfully dispatched {$dispatched} jobs!\n";
echo "\nNext steps:\n";
echo "1. Start queue worker: php artisan queue:work\n";
echo "2. Monitor progress: tail -f storage/logs/laravel.log\n";
echo "3. Check status again: php check-face-embeddings.php\n";
