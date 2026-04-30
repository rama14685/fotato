<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;

/**
 * Property-Based Test for Task 5.2: Batch Processing Completeness
 *
 * **Property 7: Batch Processing Completeness**
 * **Validates: Requirements 4.4**
 *
 * Verifies that all photo embeddings in the input collection are processed
 * and included in the results. The service must process every photo provided,
 * regardless of similarity score or threshold.
 *
 * For any collection of N photo embeddings:
 * - The result collection SHALL contain exactly N MatchResult objects
 * - Each photo ID from input SHALL appear exactly once in results
 * - No photos SHALL be skipped or lost during processing
 */
class BatchProcessingCompletenessPropertyTest extends TestCase
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
     * Helper: Create PhotoEmbeddingData with single face
     */
    private function createPhotoEmbedding(int|string $photoId, array $embedding): PhotoEmbeddingData
    {
        return new PhotoEmbeddingData($photoId, [$embedding]);
    }

    /**
     * Property: All photos in input collection are processed
     * 
     * For any collection of N photos, the result SHALL contain exactly N results,
     * one for each input photo. No photos SHALL be skipped or lost.
     *
     * **Validates: Requirements 4.4**
     */
    public function test_property_all_photos_processed(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            // Generate random number of photos (1-100)
            $photoCount = random_int(1, 100);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $expectedPhotoIds = [];

            // Create photo embeddings with unique IDs
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 1000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
                $expectedPhotoIds[] = $photoId;
            }

            // Process all photos
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify result count matches input count
            $this->assertCount(
                $photoCount,
                $results,
                "Iteration {$iteration}: Expected {$photoCount} results, got " . count($results)
            );

            // Verify all photo IDs are present in results
            $resultPhotoIds = array_map(fn($result) => $result->photoId, $results);
            $this->assertEqualsCanonicalizing(
                $expectedPhotoIds,
                $resultPhotoIds,
                "Iteration {$iteration}: Not all photos were processed. " .
                "Expected photo IDs: " . implode(', ', $expectedPhotoIds) . ", " .
                "Got: " . implode(', ', $resultPhotoIds)
            );
        }
    }

    /**
     * Property: Each photo appears exactly once in results
     * 
     * For any collection of photos, each photo ID SHALL appear exactly once
     * in the results, even if multiple faces are detected in the photo.
     *
     * **Validates: Requirements 4.4**
     */
    public function test_property_no_duplicate_photos(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            // Create photo embeddings
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 2000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            // Process all photos
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Count occurrences of each photo ID
            $photoIdCounts = [];
            foreach ($results as $result) {
                $photoIdCounts[$result->photoId] = ($photoIdCounts[$result->photoId] ?? 0) + 1;
            }

            // Verify each photo ID appears exactly once
            foreach ($photoIdCounts as $photoId => $count) {
                $this->assertEquals(
                    1,
                    $count,
                    "Iteration {$iteration}: Photo ID {$photoId} appears {$count} times, expected 1"
                );
            }
        }
    }

    /**
     * Property: Processing large batches completes successfully
     * 
     * The service SHALL successfully process large batches of photos
     * (up to 1000 photos) without losing or skipping any photos.
     *
     * **Validates: Requirements 4.4**
     */
    public function test_property_large_batch_processing(): void
    {
        for ($iteration = 1; $iteration <= 5; $iteration++) {
            // Test with large batch (500-1000 photos)
            $photoCount = random_int(500, 1000);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $expectedPhotoIds = [];

            // Create large batch of photo embeddings
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 3000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
                $expectedPhotoIds[] = $photoId;
            }

            // Process all photos
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify all photos were processed
            $this->assertCount(
                $photoCount,
                $results,
                "Iteration {$iteration}: Large batch processing failed. " .
                "Expected {$photoCount} results, got " . count($results)
            );

            // Verify all photo IDs are present
            $resultPhotoIds = array_map(fn($result) => $result->photoId, $results);
            $this->assertEqualsCanonicalizing(
                $expectedPhotoIds,
                $resultPhotoIds,
                "Iteration {$iteration}: Large batch processing lost photos"
            );
        }
    }

    /**
     * Property: Empty collection returns empty results
     * 
     * When an empty photo collection is provided, the service SHALL return
     * an empty result collection (not null, not an error).
     *
     * **Validates: Requirements 4.4**
     */
    public function test_property_empty_collection_returns_empty_results(): void
    {
        $customerEmbedding = $this->generateRandomEmbedding();
        $photoEmbeddings = [];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
