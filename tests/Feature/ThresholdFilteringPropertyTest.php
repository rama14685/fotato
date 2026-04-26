<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\FaceEmbedding;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 11.2: Threshold Filtering
 *
 * **Property 7: Threshold Filtering**
 * **Validates: Requirements 4.4**
 *
 * For any search results returned, all photos SHALL have a similarity score
 * greater than 0.6.
 */
class ThresholdFilteringPropertyTest extends TestCase
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
     * Build a 128-element embedding vector of random floats in [-1, 1].
     */
    private function makeRandomEmbedding(): array
    {
        $vector = [];
        for ($i = 0; $i < 128; $i++) {
            $vector[] = (mt_rand(-1000, 1000)) / 1000.0;
        }
        return $vector;
    }

    /**
     * Create a photo with a face embedding in the given album.
     *
     * @param int   $albumId
     * @param array $embeddingVector 128-element float array
     * @return Photo
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

    // -------------------------------------------------------------------------
    // Property 7 – every returned photo has similarity > 0.6
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Only photos with similarity > 0.6 are returned.
     *
     * Creates photos with known embeddings:
     *   - Identical to client embedding → similarity = 1.0 (ABOVE threshold)
     *   - All-zeros vector              → similarity = 0.0 (BELOW threshold)
     *
     * Verifies that only the above-threshold photos appear in the response.
     *
     * **Property 7: Threshold Filtering**
     * **Validates: Requirements 4.4**
     */
    public function test_property_all_returned_photos_have_similarity_above_threshold(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $aboveCount = rand(1, 8);
            $belowCount = rand(1, 8);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Threshold Album Scenario {$scenario}",
                'location'        => 'Jakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Photos ABOVE threshold: identical to client → similarity = 1.0
            $aboveIds = [];
            for ($i = 0; $i < $aboveCount; $i++) {
                $photo      = $this->createPhotoWithEmbedding($album->id, $clientEmbedding);
                $aboveIds[] = $photo->id;
            }

            // Photos BELOW threshold: all-zeros → similarity = 0.0
            $belowIds = [];
            for ($i = 0; $i < $belowCount; $i++) {
                $photo      = $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(0.0));
                $belowIds[] = $photo->id;
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            // Every returned photo must have similarity > 0.6
            foreach ($photos as $index => $photo) {
                $this->assertArrayHasKey(
                    'similarity',
                    $photo,
                    "Scenario {$scenario}, photo #{$index}: 'similarity' key must be present"
                );
                $this->assertGreaterThan(
                    0.6,
                    $photo['similarity'],
                    "Scenario {$scenario}, photo #{$index}: similarity {$photo['similarity']} must be > 0.6"
                );
            }

            // The number of returned photos must equal the above-threshold count
            $this->assertCount(
                $aboveCount,
                $photos,
                "Scenario {$scenario}: Expected {$aboveCount} photos above threshold, got " . count($photos)
            );

            // Below-threshold photo IDs must NOT appear in the response
            $returnedIds = array_column($photos, 'id');
            foreach ($belowIds as $belowId) {
                $this->assertNotContains(
                    $belowId,
                    $returnedIds,
                    "Scenario {$scenario}: Photo ID {$belowId} (similarity=0.0) should NOT be in results"
                );
            }
        }
    }

    /**
     * Property-Based Test: Zero-vector photos are never returned.
     *
     * Regardless of how many zero-vector photos exist in the album, none should
     * appear in the results because their similarity with any non-zero client
     * embedding is 0.0 (below the 0.6 threshold).
     *
     * **Property 7: Threshold Filtering**
     * **Validates: Requirements 4.4**
     */
    public function test_property_zero_vector_photos_never_returned(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $n = rand(3, 15);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Zero Vector Album Scenario {$scenario}",
                'location'        => 'Bandung',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones (non-zero)
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // All photos have zero-vector embeddings → similarity = 0.0
            for ($i = 0; $i < $n; $i++) {
                $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(0.0));
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json = $response->json();
            $this->assertCount(
                0,
                $json['photos'],
                "Scenario {$scenario}: Zero-vector photos should never be returned (similarity=0.0 ≤ 0.6)"
            );
        }
    }

    /**
     * Property-Based Test: Identical-vector photos are always returned.
     *
     * Photos with embeddings identical to the client embedding have similarity = 1.0,
     * which is always above the 0.6 threshold.
     *
     * **Property 7: Threshold Filtering**
     * **Validates: Requirements 4.4**
     */
    public function test_property_identical_vector_photos_always_returned(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $n = rand(1, 10);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Identical Vector Album Scenario {$scenario}",
                'location'        => 'Surabaya',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Use a random non-zero client embedding
            $clientEmbedding = $this->makeRandomEmbedding();
            // Ensure it's not all-zeros (extremely unlikely but guard anyway)
            $clientEmbedding[0] = max(0.001, abs($clientEmbedding[0]));

            // Create N photos with embeddings identical to the client
            for ($i = 0; $i < $n; $i++) {
                $this->createPhotoWithEmbedding($album->id, $clientEmbedding);
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
                "Scenario {$scenario}: All {$n} identical-vector photos should be returned"
            );

            foreach ($photos as $index => $photo) {
                $this->assertGreaterThan(
                    0.6,
                    $photo['similarity'],
                    "Scenario {$scenario}, photo #{$index}: identical-vector similarity must be > 0.6"
                );
            }
        }
    }

    /**
     * Property-Based Test: Mixed above/below threshold photos – only above are returned.
     *
     * Creates a mix of photos with known similarities and verifies the threshold
     * boundary is respected in every scenario.
     *
     * **Property 7: Threshold Filtering**
     * **Validates: Requirements 4.4**
     */
    public function test_property_threshold_boundary_respected(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Boundary Album Scenario {$scenario}",
                'location'        => 'Yogyakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Above-threshold photos: identical to client (similarity = 1.0)
            $aboveCount = rand(1, 5);
            for ($i = 0; $i < $aboveCount; $i++) {
                $this->createPhotoWithEmbedding($album->id, $clientEmbedding);
            }

            // Below-threshold photos: all-zeros (similarity = 0.0)
            $belowCount = rand(1, 5);
            for ($i = 0; $i < $belowCount; $i++) {
                $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(0.0));
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json   = $response->json();
            $photos = $json['photos'];

            // Core property: every returned photo has similarity > 0.6
            foreach ($photos as $index => $photo) {
                $this->assertGreaterThan(
                    0.6,
                    $photo['similarity'],
                    "Scenario {$scenario}, photo #{$index}: similarity {$photo['similarity']} must be > 0.6"
                );
            }

            // Exactly the above-threshold photos should be returned
            $this->assertCount(
                $aboveCount,
                $photos,
                "Scenario {$scenario}: Expected {$aboveCount} above-threshold photos, got " . count($photos)
            );
        }
    }

    /**
     * Property-Based Test: Empty album returns empty results (no threshold violations).
     *
     * **Property 7: Threshold Filtering**
     * **Validates: Requirements 4.4**
     */
    public function test_property_empty_album_returns_no_photos(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Empty Album',
            'location'        => 'Medan',
            'event_date'      => now()->subDays(5),
        ]);

        $clientEmbedding = $this->makeUniformEmbedding(1.0);

        $response = $this->postSearch($user, [
            'embedding_vector' => $clientEmbedding,
            'album_id'         => $album->id,
        ]);

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertCount(0, $json['photos'], 'Empty album should return no photos');
    }
}
