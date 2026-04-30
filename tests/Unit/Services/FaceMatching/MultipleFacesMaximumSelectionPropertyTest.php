<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;

/**
 * Property-Based Test for Task 5.5: Multiple Faces Maximum Selection
 *
 * **Property 10: Multiple Faces Maximum Selection**
 * **Validates: Requirements 5.1, 5.2, 5.3, 5.4**
 *
 * Verifies that when a photo contains multiple face embeddings, the service
 * uses the highest similarity score among all faces in that photo.
 *
 * For any photo with N face embeddings:
 * - The service SHALL calculate similarity for each face
 * - The service SHALL use the maximum similarity score
 * - The result SHALL contain only the maximum score, not all scores
 * - The photo SHALL appear only once in results
 */
class MultipleFacesMaximumSelectionPropertyTest extends TestCase
{
    private FaceMatchingService $service;
    private CosineSimilarityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CosineSimilarityCalculator();
        $this->service = new FaceMatchingService($this->calculator);
    }

    /**
     * Helper: Generate a random 128-dimensional embedding vector
     */
    private function generateRandomEmbedding(float $min = -1.0, float $max = 1.0): array
    {
        $embedding = [];
        for ($i = 0; $i < 128; $i++) {
            $embedding[] = $min + lcg_value() * ($max - $min);
        }
        return $embedding;
    }

    /**
     * Helper: Create PhotoEmbeddingData with multiple faces
     */
    private function createPhotoWithMultipleFaces(int|string $photoId, int $faceCount): PhotoEmbeddingData
    {
        $embeddings = [];
        for ($i = 0; $i < $faceCount; $i++) {
            $embeddings[] = $this->generateRandomEmbedding();
        }
        return new PhotoEmbeddingData($photoId, $embeddings);
    }

    /**
     * Property: Maximum similarity is used for photos with multiple faces
     * 
     * For a photo with multiple faces, the result SHALL contain the highest
     * similarity score among all faces in that photo.
     *
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_property_maximum_similarity_selected(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            
            // Create a photo with multiple faces
            $photoId = 1000;
            $faceCount = random_int(2, 10);
            $photoData = $this->createPhotoWithMultipleFaces($photoId, $faceCount);

            // Calculate expected maximum similarity manually
            $maxExpectedSimilarity = -2.0;
            foreach ($photoData->embeddings as $embedding) {
                $similarity = $this->calculator->calculateSimilarity($customerEmbedding, $embedding);
                $maxExpectedSimilarity = max($maxExpectedSimilarity, $similarity);
            }

            // Process the photo
            $results = $this->service->matchFaces($customerEmbedding, [$photoData]);

            // Verify result contains maximum similarity
            $this->assertCount(1, $results);
            $result = $results[0];
            
            $this->assertEqualsWithDelta(
                $maxExpectedSimilarity,
                $result->similarityScore,
                1e-10,
                "Iteration {$iteration}: Expected maximum similarity {$maxExpectedSimilarity}, " .
                "got {$result->similarityScore}"
            );
        }
    }

    /**
     * Property: Photo with multiple faces appears only once in results
     * 
     * Even if a photo contains multiple faces, it SHALL appear exactly once
     * in the results with the highest similarity score.
     *
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_property_multiple_faces_single_result(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            
            // Create multiple photos, some with multiple faces
            $photoEmbeddings = [];
            $expectedPhotoIds = [];
            
            $photoCount = random_int(5, 15);
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 2000 + $i;
                $faceCount = random_int(1, 5); // Some with 1 face, some with multiple
                $photoEmbeddings[] = $this->createPhotoWithMultipleFaces($photoId, $faceCount);
                $expectedPhotoIds[] = $photoId;
            }

            // Process all photos
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify each photo appears exactly once
            $this->assertCount(
                $photoCount,
                $results,
                "Iteration {$iteration}: Expected {$photoCount} results, got " . count($results)
            );

            $resultPhotoIds = array_map(fn($r) => $r->photoId, $results);
            $this->assertEqualsCanonicalizing(
                $expectedPhotoIds,
                $resultPhotoIds,
                "Iteration {$iteration}: Photo IDs don't match"
            );
        }
    }

    /**
     * Property: Maximum is selected even when other faces have low similarity
     * 
     * The service SHALL select the maximum similarity even if other faces
     * in the same photo have very low similarity scores.
     *
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_property_maximum_selected_with_low_alternatives(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            
            // Create a photo with one good match and several poor matches
            $photoId = 3000;
            $embeddings = [];
            
            // Add one embedding similar to customer (high similarity expected)
            $embeddings[] = $customerEmbedding; // This will have similarity = 1.0
            
            // Add several random embeddings (likely low similarity)
            for ($i = 0; $i < random_int(2, 5); $i++) {
                $embeddings[] = $this->generateRandomEmbedding();
            }
            
            $photoData = new PhotoEmbeddingData($photoId, $embeddings);

            // Process the photo
            $results = $this->service->matchFaces($customerEmbedding, [$photoData]);

            // Verify the result has high similarity (from the identical embedding)
            $this->assertCount(1, $results);
            $result = $results[0];
            
            $this->assertGreaterThanOrEqual(
                0.99, // Should be very close to 1.0
                $result->similarityScore,
                "Iteration {$iteration}: Expected high similarity from identical embedding"
            );
        }
    }

    /**
     * Property: All faces in a photo are considered
     * 
     * The service SHALL evaluate all faces in a photo, not just the first one.
     * The maximum similarity should be selected from all faces.
     *
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_property_all_faces_evaluated(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            
            // Create a photo where the best match is not the first face
            $photoId = 4000;
            $embeddings = [];
            
            // Add several random embeddings first
            for ($i = 0; $i < random_int(2, 4); $i++) {
                $embeddings[] = $this->generateRandomEmbedding();
            }
            
            // Add the best match (similar to customer) at the end
            $embeddings[] = $customerEmbedding;
            
            $photoData = new PhotoEmbeddingData($photoId, $embeddings);

            // Process the photo
            $results = $this->service->matchFaces($customerEmbedding, [$photoData]);

            // Verify the result has high similarity (from the last embedding)
            $this->assertCount(1, $results);
            $result = $results[0];
            
            $this->assertGreaterThanOrEqual(
                0.99,
                $result->similarityScore,
                "Iteration {$iteration}: Best match not selected (not all faces evaluated)"
            );
        }
    }

    /**
     * Property: Maximum selection works with large number of faces
     * 
     * The service SHALL correctly select maximum similarity even when a photo
     * contains many faces (up to 20+).
     *
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_property_maximum_with_many_faces(): void
    {
        for ($iteration = 1; $iteration <= 10; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            
            // Create a photo with many faces
            $photoId = 5000;
            $faceCount = random_int(10, 30);
            $photoData = $this->createPhotoWithMultipleFaces($photoId, $faceCount);

            // Calculate expected maximum
            $maxExpectedSimilarity = -2.0;
            foreach ($photoData->embeddings as $embedding) {
                $similarity = $this->calculator->calculateSimilarity($customerEmbedding, $embedding);
                $maxExpectedSimilarity = max($maxExpectedSimilarity, $similarity);
            }

            // Process the photo
            $results = $this->service->matchFaces($customerEmbedding, [$photoData]);

            // Verify result
            $this->assertCount(1, $results);
            $result = $results[0];
            
            $this->assertEqualsWithDelta(
                $maxExpectedSimilarity,
                $result->similarityScore,
                1e-10,
                "Iteration {$iteration}: Maximum not selected with {$faceCount} faces"
            );
        }
    }

    /**
     * Property: Threshold filtering uses maximum similarity
     * 
     * When filtering by threshold, the service SHALL use the maximum similarity
     * score from all faces in a photo to determine if it matches.
     *
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_property_threshold_uses_maximum(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            $threshold = lcg_value();
            
            // Create a photo with multiple faces
            $photoId = 6000;
            $photoData = $this->createPhotoWithMultipleFaces($photoId, random_int(2, 5));

            // Calculate maximum similarity
            $maxSimilarity = -2.0;
            foreach ($photoData->embeddings as $embedding) {
                $similarity = $this->calculator->calculateSimilarity($customerEmbedding, $embedding);
                $maxSimilarity = max($maxSimilarity, $similarity);
            }

            // Process with threshold
            $results = $this->service->matchFaces($customerEmbedding, [$photoData], $threshold);

            // Verify matchesThreshold is based on maximum
            $this->assertCount(1, $results);
            $result = $results[0];
            
            $expectedMatches = $maxSimilarity >= $threshold;
            $this->assertEquals(
                $expectedMatches,
                $result->matchesThreshold,
                "Iteration {$iteration}: Threshold filtering not using maximum similarity"
            );
        }
    }
}
