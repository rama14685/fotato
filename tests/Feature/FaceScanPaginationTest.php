<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\FaceEmbedding;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for face scan pagination functionality
 *
 * Validates: Requirements 9.3
 */
class FaceScanPaginationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper method to create a valid 128-dimensional embedding vector
     */
    private function createEmbedding(): array
    {
        $embedding = [];
        for ($i = 0; $i < 128; $i++) {
            $embedding[] = mt_rand(-100, 100) / 100.0;
        }
        return $embedding;
    }

    /**
     * Helper method to normalize an embedding vector
     */
    private function normalizeEmbedding(array $embedding): array
    {
        $magnitude = sqrt(array_sum(array_map(fn($x) => $x ** 2, $embedding)));
        if ($magnitude == 0) {
            return $embedding;
        }
        return array_map(fn($x) => $x / $magnitude, $embedding);
    }

    /**
     * Helper method to create photos with face embeddings that match the client embedding
     * (high similarity > 0.6)
     */
    private function createMatchingPhotos(Album $album, array $clientEmbedding, int $count): array
    {
        $photos = [];
        $normalizedClient = $this->normalizeEmbedding($clientEmbedding);

        for ($i = 0; $i < $count; $i++) {
            $photo = Photo::create([
                'album_id' => $album->id,
                'original_path' => "photos/original_{$i}.jpg",
                'watermark_path' => "photos/watermark_{$i}.jpg",
                'price' => 50000 + ($i * 1000),
            ]);

            // Create a similar embedding by adding small noise to client embedding
            $similarEmbedding = array_map(function ($val) {
                return $val + (mt_rand(-10, 10) / 100.0);
            }, $normalizedClient);

            $similarEmbedding = $this->normalizeEmbedding($similarEmbedding);

            FaceEmbedding::create([
                'photo_id' => $photo->id,
                'embedding_vector' => json_encode($similarEmbedding),
            ]);

            $photos[] = $photo;
        }

        return $photos;
    }

    // -------------------------------------------------------------------------
    // 1. Pagination with result sets > 50 photos
    // -------------------------------------------------------------------------

    /**
     * Test that pagination is applied when matched photos exceed 50
     *
     * Validates: Requirements 9.3
     */
    public function test_pagination_applied_when_results_exceed_50(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Large Event',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $clientEmbedding = $this->createEmbedding();

        // Create 75 matching photos (should trigger pagination)
        $this->createMatchingPhotos($album, $clientEmbedding, 75);

        $response = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'photos',
            'pagination' => [
                'current_page',
                'total_pages',
                'per_page',
                'total_items',
            ],
        ]);

        $data = $response->json();

        // First page should return exactly 50 photos
        $this->assertCount(50, $data['photos']);

        // Pagination metadata should be correct
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(2, $data['pagination']['total_pages']);
        $this->assertEquals(50, $data['pagination']['per_page']);
        $this->assertEquals(75, $data['pagination']['total_items']);
    }

    /**
     * Test that second page returns remaining photos
     *
     * Validates: Requirements 9.3
     */
    public function test_second_page_returns_remaining_photos(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Large Event',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $clientEmbedding = $this->createEmbedding();

        // Create 75 matching photos
        $this->createMatchingPhotos($album, $clientEmbedding, 75);

        // Request page 2
        $response = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 2,
        ]);

        $response->assertOk();

        $data = $response->json();

        // Second page should return remaining 25 photos
        $this->assertCount(25, $data['photos']);

        // Pagination metadata should be correct
        $this->assertEquals(2, $data['pagination']['current_page']);
        $this->assertEquals(2, $data['pagination']['total_pages']);
        $this->assertEquals(50, $data['pagination']['per_page']);
        $this->assertEquals(75, $data['pagination']['total_items']);
    }

    /**
     * Test that pagination is not applied when results are <= 50
     *
     * Validates: Requirements 9.3
     */
    public function test_no_pagination_when_results_are_50_or_less(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Small Event',
            'location' => 'Bandung',
            'event_date' => now()->subDays(3),
        ]);

        $clientEmbedding = $this->createEmbedding();

        // Create exactly 50 matching photos
        $this->createMatchingPhotos($album, $clientEmbedding, 50);

        $response = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
        ]);

        $response->assertOk();

        $data = $response->json();

        // Should return all 50 photos on first page
        $this->assertCount(50, $data['photos']);

        // Pagination metadata should indicate only 1 page
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(1, $data['pagination']['total_pages']);
        $this->assertEquals(50, $data['pagination']['per_page']);
        $this->assertEquals(50, $data['pagination']['total_items']);
    }

    // -------------------------------------------------------------------------
    // 2. Pagination metadata accuracy
    // -------------------------------------------------------------------------

    /**
     * Test pagination metadata accuracy with 100 photos
     *
     * Validates: Requirements 9.3
     */
    public function test_pagination_metadata_accuracy_with_100_photos(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Very Large Event',
            'location' => 'Surabaya',
            'event_date' => now()->subDays(7),
        ]);

        $clientEmbedding = $this->createEmbedding();

        // Create 100 matching photos
        $this->createMatchingPhotos($album, $clientEmbedding, 100);

        $response = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
        ]);

        $response->assertOk();

        $data = $response->json();

        // Verify pagination metadata
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(2, $data['pagination']['total_pages']); // 100 / 50 = 2 pages
        $this->assertEquals(50, $data['pagination']['per_page']);
        $this->assertEquals(100, $data['pagination']['total_items']);
    }

    /**
     * Test pagination metadata accuracy with 125 photos (3 pages)
     *
     * Validates: Requirements 9.3
     */
    public function test_pagination_metadata_accuracy_with_125_photos(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Huge Event',
            'location' => 'Bali',
            'event_date' => now()->subDays(10),
        ]);

        $clientEmbedding = $this->createEmbedding();

        // Create 125 matching photos
        $this->createMatchingPhotos($album, $clientEmbedding, 125);

        // Test page 1
        $response1 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 1,
        ]);

        $response1->assertOk();
        $data1 = $response1->json();

        $this->assertCount(50, $data1['photos']);
        $this->assertEquals(1, $data1['pagination']['current_page']);
        $this->assertEquals(3, $data1['pagination']['total_pages']); // ceil(125 / 50) = 3
        $this->assertEquals(125, $data1['pagination']['total_items']);

        // Test page 2
        $response2 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 2,
        ]);

        $response2->assertOk();
        $data2 = $response2->json();

        $this->assertCount(50, $data2['photos']);
        $this->assertEquals(2, $data2['pagination']['current_page']);
        $this->assertEquals(3, $data2['pagination']['total_pages']);

        // Test page 3
        $response3 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 3,
        ]);

        $response3->assertOk();
        $data3 = $response3->json();

        $this->assertCount(25, $data3['photos']); // Remaining 25 photos
        $this->assertEquals(3, $data3['pagination']['current_page']);
        $this->assertEquals(3, $data3['pagination']['total_pages']);
    }

    /**
     * Test that page parameter defaults to 1 when not provided
     *
     * Validates: Requirements 9.3
     */
    public function test_page_parameter_defaults_to_1(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Event',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $clientEmbedding = $this->createEmbedding();
        $this->createMatchingPhotos($album, $clientEmbedding, 60);

        // Request without page parameter
        $response = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
        ]);

        $response->assertOk();

        $data = $response->json();

        // Should default to page 1
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertCount(50, $data['photos']);
    }

    /**
     * Test that invalid page parameter (< 1) is rejected
     *
     * Validates: Requirements 9.3
     */
    public function test_invalid_page_parameter_is_rejected(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Event',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $clientEmbedding = $this->createEmbedding();

        // Request with invalid page parameter (0)
        $response = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    // -------------------------------------------------------------------------
    // 3. Photo ordering consistency across pages
    // -------------------------------------------------------------------------

    /**
     * Test that photos are consistently ordered across pages (by similarity descending)
     *
     * Validates: Requirements 9.3, 4.5, 6.3
     */
    public function test_photos_ordered_consistently_across_pages(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Event',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $clientEmbedding = $this->createEmbedding();
        $this->createMatchingPhotos($album, $clientEmbedding, 75);

        // Get page 1
        $response1 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 1,
        ]);

        $response1->assertOk();
        $data1 = $response1->json();

        // Get page 2
        $response2 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 2,
        ]);

        $response2->assertOk();
        $data2 = $response2->json();

        // Verify page 1 photos are sorted by similarity descending
        $page1Similarities = array_column($data1['photos'], 'similarity');
        $page1SortedDesc = $page1Similarities;
        rsort($page1SortedDesc);
        $this->assertEquals($page1SortedDesc, $page1Similarities, 'Page 1 should be sorted by similarity descending');

        // Verify page 2 photos are sorted by similarity descending
        $page2Similarities = array_column($data2['photos'], 'similarity');
        $page2SortedDesc = $page2Similarities;
        rsort($page2SortedDesc);
        $this->assertEquals($page2SortedDesc, $page2Similarities, 'Page 2 should be sorted by similarity descending');

        // Verify that the last photo on page 1 has higher similarity than the first photo on page 2
        $lastPage1Similarity = end($page1Similarities);
        $firstPage2Similarity = $page2Similarities[0];
        $this->assertGreaterThanOrEqual(
            $firstPage2Similarity,
            $lastPage1Similarity,
            'Last photo on page 1 should have higher or equal similarity to first photo on page 2'
        );
    }

    /**
     * Test that no photos are duplicated across pages
     *
     * Validates: Requirements 9.3
     */
    public function test_no_duplicate_photos_across_pages(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Event',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $clientEmbedding = $this->createEmbedding();
        $this->createMatchingPhotos($album, $clientEmbedding, 75);

        // Get page 1
        $response1 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 1,
        ]);

        $response1->assertOk();
        $data1 = $response1->json();

        // Get page 2
        $response2 = $this->actingAs($user)->postJson('/face-scan/search', [
            'embedding_vector' => $clientEmbedding,
            'album_id' => $album->id,
            'page' => 2,
        ]);

        $response2->assertOk();
        $data2 = $response2->json();

        // Extract photo IDs from both pages
        $page1Ids = array_column($data1['photos'], 'id');
        $page2Ids = array_column($data2['photos'], 'id');

        // Verify no duplicates
        $intersection = array_intersect($page1Ids, $page2Ids);
        $this->assertEmpty($intersection, 'No photo IDs should appear on both pages');

        // Verify total unique photos
        $allIds = array_merge($page1Ids, $page2Ids);
        $uniqueIds = array_unique($allIds);
        $this->assertCount(75, $uniqueIds, 'Should have 75 unique photos across both pages');
    }
}
