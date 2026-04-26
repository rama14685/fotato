<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\FaceEmbedding;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 20.1: Client Embedding Non-Persistence
 *
 * **Property 19: Client Embedding Non-Persistence**
 * **Validates: Requirements 8.2, 8.3**
 *
 * For any search operation, the client face embedding SHALL NOT be stored
 * in the database after the search completes.
 */
class ClientEmbeddingNonPersistencePropertyTest extends TestCase
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
            ->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
            ->postJson('/face-scan/search', $payload);
    }

    /**
     * Check if a given embedding vector exists in the face_embeddings table.
     *
     * @param array $embeddingVector
     * @return bool
     */
    private function embeddingExistsInDatabase(array $embeddingVector): bool
    {
        $jsonEmbedding = json_encode($embeddingVector);
        
        // Check if any face_embedding has this exact embedding_vector
        return FaceEmbedding::where('embedding_vector', $jsonEmbedding)->exists();
    }

    // -------------------------------------------------------------------------
    // Property 19 – Client embeddings are never persisted to database
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Client embeddings are never stored in database.
     *
     * Creates albums with photo embeddings, performs multiple search operations
     * with different client embeddings, and verifies that:
     * 1. The count of face_embeddings remains unchanged after searches
     * 2. No client embeddings appear in the database
     *
     * **Property 19: Client Embedding Non-Persistence**
     * **Validates: Requirements 8.2, 8.3**
     */
    public function test_property_client_embeddings_never_persisted(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            // Create album with random number of photos
            $photoCount = rand(3, 10);

            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Privacy Test Album Scenario {$scenario}",
                'location'        => 'Jakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Create photos with embeddings (these are photographer's pre-computed embeddings)
            for ($i = 0; $i < $photoCount; $i++) {
                $this->createPhotoWithEmbedding($album->id, $this->makeRandomEmbedding());
            }

            // Count face_embeddings BEFORE search
            $embeddingCountBefore = FaceEmbedding::count();

            // Generate unique client embeddings for this scenario
            $clientEmbeddings = [];
            $searchCount = rand(2, 5);
            
            for ($i = 0; $i < $searchCount; $i++) {
                $clientEmbedding = $this->makeRandomEmbedding();
                $clientEmbeddings[] = $clientEmbedding;

                // Verify client embedding does NOT exist in database before search
                $this->assertFalse(
                    $this->embeddingExistsInDatabase($clientEmbedding),
                    "Scenario {$scenario}, search #{$i}: Client embedding should NOT exist in database before search"
                );

                // Perform search with client embedding
                $response = $this->postSearch($user, [
                    'embedding_vector' => $clientEmbedding,
                    'album_id'         => $album->id,
                ]);

                $response->assertStatus(200);

                // Verify client embedding does NOT exist in database after search
                $this->assertFalse(
                    $this->embeddingExistsInDatabase($clientEmbedding),
                    "Scenario {$scenario}, search #{$i}: Client embedding should NOT exist in database after search"
                );
            }

            // Count face_embeddings AFTER all searches
            $embeddingCountAfter = FaceEmbedding::count();

            // Core property: embedding count must remain unchanged
            $this->assertEquals(
                $embeddingCountBefore,
                $embeddingCountAfter,
                "Scenario {$scenario}: Face embedding count changed from {$embeddingCountBefore} to {$embeddingCountAfter}. " .
                "Client embeddings should NEVER be persisted to database."
            );

            // Double-check: verify none of the client embeddings exist in database
            foreach ($clientEmbeddings as $index => $clientEmbedding) {
                $this->assertFalse(
                    $this->embeddingExistsInDatabase($clientEmbedding),
                    "Scenario {$scenario}, client embedding #{$index}: Should NOT be found in database after all searches"
                );
            }
        }
    }

    /**
     * Property-Based Test: Database only contains photo embeddings, never client embeddings.
     *
     * Performs searches and verifies that all embeddings in the database belong to
     * photos (have valid photo_id relationships), not client embeddings.
     *
     * **Property 19: Client Embedding Non-Persistence**
     * **Validates: Requirements 8.2, 8.3**
     */
    public function test_property_database_only_contains_photo_embeddings(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Photo Embedding Only Scenario {$scenario}",
                'location'        => 'Bandung',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Create photos with embeddings
            $photoCount = rand(2, 8);
            $photoIds = [];
            
            for ($i = 0; $i < $photoCount; $i++) {
                $photo = $this->createPhotoWithEmbedding($album->id, $this->makeRandomEmbedding());
                $photoIds[] = $photo->id;
            }

            // Perform multiple searches with different client embeddings
            $searchCount = rand(3, 7);
            
            for ($i = 0; $i < $searchCount; $i++) {
                $clientEmbedding = $this->makeRandomEmbedding();

                $response = $this->postSearch($user, [
                    'embedding_vector' => $clientEmbedding,
                    'album_id'         => $album->id,
                ]);

                $response->assertStatus(200);
            }

            // Verify ALL face_embeddings in database have valid photo_id relationships
            $allEmbeddings = FaceEmbedding::all();
            
            foreach ($allEmbeddings as $embedding) {
                $this->assertNotNull(
                    $embedding->photo_id,
                    "Scenario {$scenario}: All face_embeddings must have a photo_id (found null)"
                );

                $this->assertTrue(
                    Photo::where('id', $embedding->photo_id)->exists(),
                    "Scenario {$scenario}: Face embedding photo_id {$embedding->photo_id} must reference a valid photo"
                );
            }

            // Verify the count matches our created photos
            $embeddingsInAlbum = FaceEmbedding::whereHas('photo', function ($query) use ($album) {
                $query->where('album_id', $album->id);
            })->count();

            $this->assertEquals(
                $photoCount,
                $embeddingsInAlbum,
                "Scenario {$scenario}: Expected {$photoCount} photo embeddings in album, found {$embeddingsInAlbum}"
            );
        }
    }

    /**
     * Property-Based Test: Successful searches never increase embedding count.
     *
     * Verifies that even when searches return matches, the client embedding
     * is never persisted to the database.
     *
     * **Property 19: Client Embedding Non-Persistence**
     * **Validates: Requirements 8.2, 8.3**
     */
    public function test_property_successful_searches_never_increase_embedding_count(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Successful Search Scenario {$scenario}",
                'location'        => 'Surabaya',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Create photos with identical embeddings to ensure matches
            $photoEmbedding = $this->makeUniformEmbedding(1.0);
            $photoCount = rand(2, 6);
            
            for ($i = 0; $i < $photoCount; $i++) {
                $this->createPhotoWithEmbedding($album->id, $photoEmbedding);
            }

            $embeddingCountBefore = FaceEmbedding::count();

            // Use the same embedding as client (will produce matches)
            $clientEmbedding = $photoEmbedding;

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            // Verify search was successful (returned matches)
            $json = $response->json();
            $this->assertTrue($json['success']);
            $this->assertGreaterThan(
                0,
                count($json['photos']),
                "Scenario {$scenario}: Search should return matches"
            );

            $embeddingCountAfter = FaceEmbedding::count();

            // Core property: even with successful matches, embedding count unchanged
            $this->assertEquals(
                $embeddingCountBefore,
                $embeddingCountAfter,
                "Scenario {$scenario}: Successful search should NOT increase embedding count"
            );
        }
    }

    /**
     * Property-Based Test: Failed searches never persist client embeddings.
     *
     * Verifies that even when searches fail (no matches), the client embedding
     * is never persisted to the database.
     *
     * **Property 19: Client Embedding Non-Persistence**
     * **Validates: Requirements 8.2, 8.3**
     */
    public function test_property_failed_searches_never_persist_embeddings(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Failed Search Scenario {$scenario}",
                'location'        => 'Yogyakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Create photos with zero embeddings (will not match non-zero client)
            $photoCount = rand(2, 6);
            
            for ($i = 0; $i < $photoCount; $i++) {
                $this->createPhotoWithEmbedding($album->id, $this->makeUniformEmbedding(0.0));
            }

            $embeddingCountBefore = FaceEmbedding::count();

            // Use non-zero client embedding (will produce no matches)
            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            // Verify search returned no matches
            $json = $response->json();
            $this->assertTrue($json['success']);
            $this->assertCount(
                0,
                $json['photos'],
                "Scenario {$scenario}: Search should return no matches"
            );

            $embeddingCountAfter = FaceEmbedding::count();

            // Core property: even with failed search, embedding count unchanged
            $this->assertEquals(
                $embeddingCountBefore,
                $embeddingCountAfter,
                "Scenario {$scenario}: Failed search should NOT increase embedding count"
            );

            // Verify client embedding does not exist in database
            $this->assertFalse(
                $this->embeddingExistsInDatabase($clientEmbedding),
                "Scenario {$scenario}: Client embedding should NOT exist in database after failed search"
            );
        }
    }

    /**
     * Property-Based Test: Multiple concurrent searches never persist client embeddings.
     *
     * Simulates multiple users performing searches simultaneously and verifies
     * that none of their client embeddings are persisted.
     *
     * **Property 19: Client Embedding Non-Persistence**
     * **Validates: Requirements 8.2, 8.3**
     */
    public function test_property_concurrent_searches_never_persist_embeddings(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Concurrent Search Scenario {$scenario}",
                'location'        => 'Medan',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Create photos with embeddings
            $photoCount = rand(3, 8);
            
            for ($i = 0; $i < $photoCount; $i++) {
                $this->createPhotoWithEmbedding($album->id, $this->makeRandomEmbedding());
            }

            $embeddingCountBefore = FaceEmbedding::count();

            // Simulate multiple users searching (each user has their own rate limit)
            $userCount = rand(3, 7);
            $clientEmbeddings = [];
            
            for ($i = 0; $i < $userCount; $i++) {
                $user = User::factory()->create();
                
                $clientEmbedding = $this->makeRandomEmbedding();
                $clientEmbeddings[] = $clientEmbedding;

                $response = $this->postSearch($user, [
                    'embedding_vector' => $clientEmbedding,
                    'album_id'         => $album->id,
                ]);

                $response->assertStatus(200);
            }

            $embeddingCountAfter = FaceEmbedding::count();

            // Core property: multiple searches should not change embedding count
            $this->assertEquals(
                $embeddingCountBefore,
                $embeddingCountAfter,
                "Scenario {$scenario}: {$userCount} concurrent searches should NOT increase embedding count"
            );

            // Verify none of the client embeddings exist in database
            foreach ($clientEmbeddings as $index => $clientEmbedding) {
                $this->assertFalse(
                    $this->embeddingExistsInDatabase($clientEmbedding),
                    "Scenario {$scenario}, user #{$index}: Client embedding should NOT exist in database"
                );
            }
        }
    }
}
