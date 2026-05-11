<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Photo;
use App\Models\FaceEmbedding;

echo "=== Face Embeddings Status ===\n\n";

$photosTotal = Photo::count();
$photosWithFaces = Photo::whereHas('faceEmbedding')->count();
$photosWithoutFaces = Photo::whereDoesntHave('faceEmbedding')->count();

echo "Total Photos: {$photosTotal}\n";
echo "Photos WITH face embeddings: {$photosWithFaces}\n";
echo "Photos WITHOUT face embeddings: {$photosWithoutFaces}\n\n";

if ($photosWithoutFaces > 0) {
    echo "⚠️  You have {$photosWithoutFaces} photos without face embeddings!\n";
    echo "These photos need to be processed with face detection.\n\n";
    
    echo "To process them, you need to:\n";
    echo "1. Install Python and face_recognition library\n";
    echo "2. Start queue worker: php artisan queue:work\n";
    echo "3. Dispatch jobs for photos\n\n";
    
    // Show sample photos without embeddings
    $samplePhotos = Photo::whereDoesntHave('faceEmbedding')->limit(5)->get();
    echo "Sample photos without embeddings:\n";
    foreach ($samplePhotos as $photo) {
        echo "  - Photo ID: {$photo->id}, Album ID: {$photo->album_id}, Path: {$photo->original_path}\n";
    }
}

echo "\n=== Face Embeddings Table ===\n";
$totalEmbeddings = FaceEmbedding::count();
echo "Total face embeddings in database: {$totalEmbeddings}\n";
