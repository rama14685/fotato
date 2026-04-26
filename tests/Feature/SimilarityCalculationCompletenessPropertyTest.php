<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\FaceEmbedding;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 11.1: Similarity Calculation Completeness
 *
 * **Property 6: Similarity Calculation Completeness**
 * **Validates: Requirements 4.3**
 *
 * For any album with N face embeddings, the face matching service SHALL calculate
 * cosine similarity for all N embeddings against the client embedding.
 */
class SimilarityCalculationCompletenessPropertyTest extends TestCase
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
     * @param int $albumId
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
    // Property 6 – similarity is calculated for ALL N embeddings in the album
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: All N photos with embeddings in an album are processed.
     *
     * Uses identical vectors (similarity = 1.0) so every photo passes the threshold,
     * allowing us to verify that the count of returned photos equals N.
     *
     * **Property 6: Similarity Calculation Completeness**
     * **Validates: Requirements 4.3**
     */
    public function test_property_similarity_calculated_for_all_embeddings_in_album(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Run multiple scenarios with varying N
        $scenarios = [1, 3, 5, 10, 15];

        foreach ($scenarios as $n) {
            // Fresh album per scenario
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Album N={$n}",
                'location'        => 'Jakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones vector
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Create N photos, each with an identical embedding to the client
            // → cosine similarity = 1.0 for every photo (all pass threshold 0.6)
            for ($i = 0; $i < $n; $i++) {
                $this->createPhotoWithEmbedding($album->id, $clientEmbedding);
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json = $response->json();
            $this->assertTrue($json['success'], "Scenario N={$n}: response should be successful");

            $returnedCount = count($json['photos']);

            $this->assertEquals(
                $n,
                $returnedCount,
                "Scenario N={$n}: Expected {$n} photos (similarity calculated for all), got {$returnedCount}"
            );

            // Verify each returned photo has a similarity value (calculation was performed)
            foreach ($json['photos'] as $index => $photo) {
                $this->assertArrayHasKey(
                    'similarity',
                    $photo,
                    "Scenario N={$n}, photo #{$index}: 'similarity' key must be present"
                );
                $this->assertIsFloat(
                    $photo['similarity'],
                    "Scenario N={$n}, photo #{$index}: similarity must be a float"
                );
            }
        }
    }

    /**
     * Property-Based Test: Random N (5–20) photos all have similarity calculated.
     *
     * Uses identical vectors so all photos pass the threshold, confirming that
     * the service processes every embedding in the album.
     *
     * **Property 6: Similarity Calculation Completeness**
     * **Validates: Requirements 4.3**
     */
    public function test_property_similarity_calculated_for_random_n_embeddings(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $n = rand(5, 20);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Random Album Scenario {$scenario}",
                'location'        => 'Bandung',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones vector
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Create N photos with identical embeddings (all will match)
            for ($i = 0; $i < $n; $i++) {
                $this->createPhotoWithEmbedding($album->id, $clientEmbedding);
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json          = $response->json();
            $returnedCount = count($json['photos']);

            $this->assertEquals(
                $n,
                $returnedCount,
                "Scenario {$scenario} (N={$n}): All {$n} embeddings should be processed; got {$returnedCount}"
            );

            // Every returned photo must carry a similarity score
            foreach ($json['photos'] as $index => $photo) {
                $this->assertArrayHasKey(
                    'similarity',
                    $photo,
                    "Scenario {$scenario}, photo #{$index}: similarity key must exist"
                );
            }
        }
    }

    /**
     * Property-Based Test: Photos WITHOUT embeddings are excluded from calculation.
     *
     * Creates a mix of photos with and without embeddings. Only photos that have
     * embeddings should be processed; the count of returned photos (all identical
     * vectors → similarity 1.0) must equal the number of photos WITH embeddings.
     *
     * **Property 6: Similarity Calculation Completeness**
     * **Validates: Requirements 4.3**
     */
    public function test_property_only_photos_with_embeddings_are_processed(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $withEmbedding    = rand(2, 8);
            $withoutEmbedding = rand(1, 5);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Mixed Album Scenario {$scenario}",
                'location'        => 'Surabaya',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Photos WITH embeddings (identical to client → similarity 1.0)
            for ($i = 0; $i < $withEmbedding; $i++) {
                $this->createPhotoWithEmbedding($album->id, $clientEmbedding);
            }

            // Photos WITHOUT embeddings (should be ignored by the service)
            for ($i = 0; $i < $withoutEmbedding; $i++) {
                Photo::create([
                    'album_id'       => $album->id,
                    'original_path'  => 'photos/original/no-embed.jpg',
                    'watermark_path' => 'photos/watermark/no-embed.jpg',
                    'price'          => 30000,
                ]);
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json          = $response->json();
            $returnedCount = count($json['photos']);

            $this->assertEquals(
                $withEmbedding,
                $returnedCount,
                "Scenario {$scenario}: Expected {$withEmbedding} photos (those with embeddings), got {$returnedCount}"
            );
        }
    }

    /**
     * Property-Based Test: Zero-vector embeddings are processed (similarity = 0, below threshold).
     *
     * Confirms that the service still iterates over all N embeddings even when
     * none pass the threshold. We verify this indirectly: the response is 200
     * with an empty photos array (all processed, none matched).
     *
     * **Property 6: Similarity Calculation Completeness**
     * **Validates: Requirements 4.3**
     */
    public function test_property_all_embeddings_processed_even_when_none_match(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $n = rand(3, 10);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "No Match Album Scenario {$scenario}",
                'location'        => 'Medan',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Client embedding: all-ones
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Photo embeddings: all-zeros → cosine similarity = 0 (below threshold 0.6)
            $zeroEmbedding = $this->makeUniformEmbedding(0.0);
            for ($i = 0; $i < $n; $i++) {
                $this->createPhotoWithEmbedding($album->id, $zeroEmbedding);
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json = $response->json();
            $this->assertTrue($json['success'], "Scenario {$scenario}: response should be successful");

            // All N embeddings were processed but none passed the threshold
            $this->assertCount(
                0,
                $json['photos'],
                "Scenario {$scenario}: Zero-vector embeddings should all be below threshold; expected 0 results"
            );
        }
    }
}
