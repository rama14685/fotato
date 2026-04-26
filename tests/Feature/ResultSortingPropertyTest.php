<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\FaceEmbedding;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 12.1: Result Sorting
 *
 * **Property 8: Result Sorting**
 * **Validates: Requirements 4.5, 6.3**
 *
 * For any set of matched photos returned, they SHALL be sorted by similarity
 * score in descending order (highest similarity first).
 */
class ResultSortingPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a 128-element embedding vector filled with the given value.
     */
    private function makeUniformEmbedding(float $value): array
    {
        return array_fill(0, 128, $value);
    }

    /**
     * Build a 128-element embedding where the first $onesCount elements are 1.0
     * and the rest are 0.0.
     *
     * When compared against an all-ones client embedding, the cosine similarity
     * equals sqrt($onesCount / 128), giving predictable, distinct scores.
     *
     * Examples (client = all-ones, 128 dims):
     *   onesCount=128 → similarity = 1.0
     *   onesCount=64  → similarity ≈ 0.707
     *   onesCount=32  → similarity ≈ 0.500  (below threshold 0.6, excluded)
     *   onesCount=1   → similarity ≈ 0.088  (below threshold, excluded)
     */
    private function makePartialOnesEmbedding(int $onesCount): array
    {
        $vector = array_fill(0, 128, 0.0);
        for ($i = 0; $i < $onesCount; $i++) {
            $vector[$i] = 1.0;
        }
        return $vector;
    }

    /**
     * Create a photo with a face embedding in the given album.
     */
    private function createPhotoWithEmbedding(int $albumId, array $embeddingVector): Photo
    {
        $photo = Photo::create([
            'album_id'       => $albumId,
            'original_path'  => 'photos/original/test.jpg',
            'watermark_path' => 'photos/watermark/test.jpg',
            'price'          => 50000,
        ]);

        FaceEmbedding::create([
            'photo_id'         => $photo->id,
            'embedding_vector' => json_encode($embeddingVector),
        ]);

        return $photo;
    }

    /**
     * POST to /face-scan/search as an authenticated user and return the response.
     */
    private function postSearch(User $user, array $payload)
    {
        return $this
            ->actingAs($user)
            ->postJson('/face-scan/search', $payload);
    }

    /**
     * Assert that the given photos array is sorted by similarity in descending order.
     */
    private function assertSortedBySimDescending(array $photos, string $context = ''): void
    {
        $count = count($photos);
        for ($i = 0; $i < $count - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $photos[$i + 1]['similarity'],
                $photos[$i]['similarity'],
                "{$context}: photos[{$i}].similarity ({$photos[$i]['similarity']}) "
                . "must be >= photos[" . ($i + 1) . "].similarity ({$photos[$i + 1]['similarity']})"
            );
        }
    }

    // -------------------------------------------------------------------------
    // Property 8 – results are always sorted by similarity descending
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Two photos with distinct similarities are returned in
     * descending order.
     *
     * Client embedding: all-ones (128 dims)
     * Photo A: all-ones          → similarity = 1.0  (highest)
     * Photo B: first 64 ones     → similarity ≈ 0.707 (lower, but > 0.6)
     *
     * **Property 8: Result Sorting**
     * **Validates: Requirements 4.5, 6.3**
     */
    public function test_property_two_photos_sorted_descending(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Sorting Album Scenario {$scenario}",
                'location'        => 'Jakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Photo A: identical to client → similarity = 1.0
            $photoA = $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(1.0));

            // Photo B: first 64 elements = 1.0, rest = 0.0 → similarity ≈ 0.707
            $photoB = $this->createPhotoWithEmbedding($album->id, $this->makePartialOnesEmbedding(64));

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            $this->assertCount(2, $photos, "Scenario {$scenario}: Both photos should be returned");

            // Core property: sorted descending
            $this->assertSortedBySimDescending($photos, "Scenario {$scenario}");

            // Photo A (similarity=1.0) must come before Photo B (similarity≈0.707)
            $this->assertEquals(
                $photoA->id,
                $photos[0]['id'],
                "Scenario {$scenario}: Photo A (similarity=1.0) must be first"
            );
            $this->assertEquals(
                $photoB->id,
                $photos[1]['id'],
                "Scenario {$scenario}: Photo B (similarity≈0.707) must be second"
            );
        }
    }

    /**
     * Property-Based Test: Three photos with distinct similarities are returned
     * in descending order.
     *
     * Client embedding: all-ones (128 dims)
     * Photo A: all-ones (128)    → similarity = 1.0
     * Photo B: first 64 ones     → similarity ≈ 0.707
     * Photo C: first 50 ones     → similarity ≈ 0.625  (> 0.6, included)
     * Photo D: first 32 ones     → similarity ≈ 0.500  (≤ 0.6, excluded)
     *
     * **Property 8: Result Sorting**
     * **Validates: Requirements 4.5, 6.3**
     */
    public function test_property_three_photos_sorted_descending(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Three Photo Sorting Scenario {$scenario}",
                'location'        => 'Bandung',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Photo A: similarity = 1.0
            $photoA = $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(1.0));

            // Photo B: similarity ≈ 0.707 (sqrt(64/128))
            $photoB = $this->createPhotoWithEmbedding($album->id, $this->makePartialOnesEmbedding(64));

            // Photo C: similarity ≈ 0.625 (sqrt(50/128)) — above threshold
            $photoC = $this->createPhotoWithEmbedding($album->id, $this->makePartialOnesEmbedding(50));

            // Photo D: similarity ≈ 0.500 (sqrt(32/128)) — below threshold, excluded
            $this->createPhotoWithEmbedding($album->id, $this->makePartialOnesEmbedding(32));

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            // Only A, B, C should be returned (D is below threshold)
            $this->assertCount(3, $photos, "Scenario {$scenario}: Photos A, B, C should be returned");

            // Core property: sorted descending
            $this->assertSortedBySimDescending($photos, "Scenario {$scenario}");

            // Verify order: A first, B second, C third
            $this->assertEquals($photoA->id, $photos[0]['id'], "Scenario {$scenario}: Photo A must be first");
            $this->assertEquals($photoB->id, $photos[1]['id'], "Scenario {$scenario}: Photo B must be second");
            $this->assertEquals($photoC->id, $photos[2]['id'], "Scenario {$scenario}: Photo C must be third");
        }
    }

    /**
     * Property-Based Test: Random N photos with distinct similarities are always
     * returned in descending order.
     *
     * Creates N photos with embeddings that have onesCount = 128, 100, 80, 70, 65
     * (all producing similarity > 0.6 against an all-ones client embedding).
     * Verifies the returned array is sorted descending for every scenario.
     *
     * **Property 8: Result Sorting**
     * **Validates: Requirements 4.5, 6.3**
     */
    public function test_property_random_n_photos_always_sorted_descending(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // onesCount values that all produce similarity > 0.6 against all-ones client:
        // sqrt(onesCount/128) > 0.6  →  onesCount > 0.36 * 128 = 46.08  →  onesCount >= 47
        $onesCounts = [128, 110, 90, 75, 65, 55, 50, 48];

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            // Pick a random subset of onesCount values (at least 2)
            $n = rand(2, count($onesCounts));
            $selectedCounts = array_slice($onesCounts, 0, $n);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Random N Sorting Scenario {$scenario}",
                'location'        => 'Surabaya',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Create photos in shuffled order to ensure sorting is not just insertion order
            $shuffledCounts = $selectedCounts;
            shuffle($shuffledCounts);

            foreach ($shuffledCounts as $onesCount) {
                $this->createPhotoWithEmbedding($album->id, $this->makePartialOnesEmbedding($onesCount));
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            $this->assertCount(
                $n,
                $photos,
                "Scenario {$scenario}: All {$n} photos should be returned (all above threshold)"
            );

            // Core property: for any N returned photos, photos[i].similarity >= photos[i+1].similarity
            $this->assertSortedBySimDescending($photos, "Scenario {$scenario}");
        }
    }

    /**
     * Property-Based Test: Single photo result is trivially sorted.
     *
     * A single-element result is always "sorted". Verifies the response structure
     * is correct and the property holds for the degenerate case.
     *
     * **Property 8: Result Sorting**
     * **Validates: Requirements 4.5, 6.3**
     */
    public function test_property_single_photo_result_is_sorted(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Single Photo Scenario {$scenario}",
                'location'        => 'Yogyakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // One photo with similarity = 1.0
            $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(1.0));

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            $this->assertCount(1, $photos, "Scenario {$scenario}: One photo should be returned");
            $this->assertSortedBySimDescending($photos, "Scenario {$scenario}");
        }
    }

    /**
     * Property-Based Test: Empty result set is trivially sorted.
     *
     * When no photos match the threshold, the empty array satisfies the sorting
     * property vacuously.
     *
     * **Property 8: Result Sorting**
     * **Validates: Requirements 4.5, 6.3**
     */
    public function test_property_empty_result_is_sorted(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Empty Result Album',
            'location'        => 'Medan',
            'event_date'      => now()->subDays(10),
        ]);

        $clientEmbedding = $this->makeUniformEmbedding(1.0);

        // All photos below threshold (zero vectors)
        for ($i = 0; $i < 5; $i++) {
            $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(0.0));
        }

        $response = $this->postSearch($user, [
            'embedding_vector' => $clientEmbedding,
            'album_id'         => $album->id,
        ]);

        $response->assertStatus(200);

        $json   = $response->json();
        $photos = $json['photos'];

        $this->assertCount(0, $photos, 'No photos should be returned when all are below threshold');
        $this->assertSortedBySimDescending($photos, 'Empty result');
        $this->assertTrue($json['success'], 'Response success flag must be true');
    }

    /**
     * Property-Based Test: Photos inserted in reverse similarity order are still
     * returned sorted descending.
     *
     * Inserts photos from lowest to highest similarity to confirm the sort is
     * not dependent on insertion order.
     *
     * **Property 8: Result Sorting**
     * **Validates: Requirements 4.5, 6.3**
     */
    public function test_property_insertion_order_does_not_affect_sort(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Insertion Order Scenario {$scenario}",
                'location'        => 'Semarang',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Insert in ascending similarity order (lowest first)
            // onesCount=50 → similarity ≈ 0.625
            // onesCount=64 → similarity ≈ 0.707
            // onesCount=128 → similarity = 1.0
            $insertionOrder = [50, 64, 128];
            foreach ($insertionOrder as $onesCount) {
                $this->createPhotoWithEmbedding($album->id, $this->makePartialOnesEmbedding($onesCount));
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            $this->assertCount(3, $photos, "Scenario {$scenario}: All 3 photos should be returned");

            // Core property: sorted descending regardless of insertion order
            $this->assertSortedBySimDescending($photos, "Scenario {$scenario}");

            // Highest similarity (onesCount=128, sim=1.0) must be first
            $this->assertGreaterThanOrEqual(
                $photos[1]['similarity'],
                $photos[0]['similarity'],
                "Scenario {$scenario}: First photo must have highest similarity"
            );
        }
    }
}
