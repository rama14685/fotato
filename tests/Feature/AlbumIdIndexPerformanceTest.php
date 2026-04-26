<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Performance tests for Task 16: Database indexing on photos.album_id
 *
 * Validates: Requirement 9.1
 *
 * The photos.album_id column is indexed via the foreign key constraint
 * defined in the create_photos_table migration:
 *   $table->foreignId('album_id')->constrained('albums')->cascadeOnDelete();
 * Laravel's foreignId()->constrained() automatically creates an index on album_id.
 */
class AlbumIdIndexPerformanceTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createAlbum(User $photographer, string $title = 'Test Album'): Album
    {
        return Album::create([
            'photographer_id' => $photographer->id,
            'title'           => $title,
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(rand(1, 365)),
        ]);
    }

    private function createPhotos(int $albumId, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            Photo::create([
                'album_id'       => $albumId,
                'original_path'  => "photos/original/photo_{$i}.jpg",
                'watermark_path' => "photos/watermark/photo_{$i}.jpg",
                'price'          => rand(10000, 100000),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Index existence verification
    // -------------------------------------------------------------------------

    /**
     * Test that the photos table has an index on album_id.
     *
     * The foreignId()->constrained() migration creates an index automatically.
     * We verify this by checking the database schema.
     *
     * Validates: Requirement 9.1
     */
    public function test_photos_album_id_column_is_indexed(): void
    {
        $databaseName = \DB::getDatabaseName();
        
        // MySQL/MariaDB: check via INFORMATION_SCHEMA
        $indexes = \DB::select(
            "SELECT INDEX_NAME, COLUMN_NAME 
             FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'photos' 
             AND COLUMN_NAME = 'album_id'",
            [$databaseName]
        );

        $this->assertNotEmpty(
            $indexes,
            'photos.album_id should be indexed for efficient album-based queries. ' .
            'The foreignId()->constrained() migration should create an index automatically.'
        );

        // Verify at least one index covers album_id
        $indexNames = array_unique(array_column($indexes, 'INDEX_NAME'));
        $this->assertNotEmpty(
            $indexNames,
            'At least one index should exist on photos.album_id'
        );
    }

    // -------------------------------------------------------------------------
    // Query correctness with large datasets
    // -------------------------------------------------------------------------

    /**
     * Test that photo queries by album_id return correct results with 100 photos per album.
     *
     * Validates: Requirement 9.1
     */
    public function test_photo_query_by_album_id_returns_correct_count(): void
    {
        $photographer = User::factory()->create();

        $albumA = $this->createAlbum($photographer, 'Album A');
        $albumB = $this->createAlbum($photographer, 'Album B');

        $this->createPhotos($albumA->id, 100);
        $this->createPhotos($albumB->id, 50);

        $countA = Photo::where('album_id', $albumA->id)->count();
        $countB = Photo::where('album_id', $albumB->id)->count();

        $this->assertEquals(100, $countA, 'Album A should have 100 photos');
        $this->assertEquals(50, $countB, 'Album B should have 50 photos');
    }

    /**
     * Test that photo queries by album_id only return photos from the specified album.
     *
     * Validates: Requirement 9.1
     */
    public function test_photo_query_by_album_id_returns_only_correct_album_photos(): void
    {
        $photographer = User::factory()->create();

        $albumA = $this->createAlbum($photographer, 'Album A');
        $albumB = $this->createAlbum($photographer, 'Album B');

        $this->createPhotos($albumA->id, 30);
        $this->createPhotos($albumB->id, 20);

        $photosA = Photo::where('album_id', $albumA->id)->get();

        foreach ($photosA as $photo) {
            $this->assertEquals(
                $albumA->id,
                $photo->album_id,
                "All photos returned for Album A should belong to Album A"
            );
        }
    }

    /**
     * Test query performance: querying 200 photos by album_id completes within 2 seconds.
     *
     * Validates: Requirement 9.1
     */
    public function test_photo_query_by_album_id_completes_within_time_limit(): void
    {
        $photographer = User::factory()->create();
        $album = $this->createAlbum($photographer, 'Large Album');

        $this->createPhotos($album->id, 200);

        $start = microtime(true);
        $photos = Photo::where('album_id', $album->id)->get();
        $elapsed = microtime(true) - $start;

        $this->assertCount(200, $photos, 'Should retrieve all 200 photos');
        $this->assertLessThan(
            2.0,
            $elapsed,
            "Query for 200 photos by album_id should complete within 2 seconds (took {$elapsed}s)"
        );
    }

    /**
     * Test that querying photos with whereHas('faceEmbedding') and album_id is efficient.
     *
     * This mirrors the actual query used in FaceScanController::search().
     *
     * Validates: Requirement 9.1
     */
    public function test_face_scan_search_query_is_efficient_with_index(): void
    {
        $photographer = User::factory()->create();
        $album = $this->createAlbum($photographer, 'Face Scan Album');

        // Create 100 photos without embeddings
        $this->createPhotos($album->id, 100);

        $start = microtime(true);
        $photos = Photo::where('album_id', $album->id)
            ->whereHas('faceEmbedding')
            ->with('faceEmbedding')
            ->get();
        $elapsed = microtime(true) - $start;

        // No photos have embeddings, so result should be empty
        $this->assertCount(0, $photos, 'No photos should have embeddings');
        $this->assertLessThan(
            2.0,
            $elapsed,
            "Face scan query should complete within 2 seconds (took {$elapsed}s)"
        );
    }

    /**
     * Test that multiple albums can be queried independently and efficiently.
     *
     * Validates: Requirement 9.1
     */
    public function test_multiple_album_queries_are_independent_and_correct(): void
    {
        $photographer = User::factory()->create();

        $albums = [];
        $expectedCounts = [10, 25, 50, 75, 100];

        foreach ($expectedCounts as $i => $count) {
            $album = $this->createAlbum($photographer, "Album {$i}");
            $this->createPhotos($album->id, $count);
            $albums[] = ['album' => $album, 'expected' => $count];
        }

        foreach ($albums as $entry) {
            $actual = Photo::where('album_id', $entry['album']->id)->count();
            $this->assertEquals(
                $entry['expected'],
                $actual,
                "Album '{$entry['album']->title}' should have {$entry['expected']} photos"
            );
        }
    }
}
