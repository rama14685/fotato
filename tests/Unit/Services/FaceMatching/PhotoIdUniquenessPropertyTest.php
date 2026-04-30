<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;

/**
 * Property-Based Test for Task 5.6: Photo ID Uniqueness
 *
 * **Property 11: Photo ID Uniqueness**
 * **Validates: Requirements 5.5**
 *
 * Verifies that each photo appears only once in the results, regardless of
 * how many faces are detected in that photo. No duplicate photo IDs should
 * appear in the result collection.
 *
 * For any collection of photos:
 * - Each photo ID SHALL appear at most once in results
 * - No duplicate photo IDs SHALL exist in results
 * - The result collection size SHALL equal the input collection size
 */
class PhotoIdUniquenessPropertyTest extends TestCase
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
     * Helper: Create PhotoEmbeddingData with variable number of faces
     */
    private function createPhotoWithFaces(int|string $photoId, int $faceCount): PhotoEmbeddingData
    {
        $embeddings = [];
        for ($i = 0; $i < $faceCount; $i++) {
            $embeddings[] = $this->generateRandomEmbedding();
        }
        return new PhotoEmbeddingData($photoId, $embeddings);
    }

    /**
     * Property: No duplicate photo IDs in results
     * 
     * For any collection of photos, each photo ID SHALL appear at most once
     * in the results, even if the photo contains multiple faces.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_no_duplicate_photo_ids(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            // Create photos with varying numbers of faces
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 1000 + $i;
                $faceCount = random_int(1, 5);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            // Process all photos
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Count occurrences of each photo ID
            $photoIdCounts = [];
            foreach ($results as $result) {
                $photoId = $result->photoId;
                $photoIdCounts[$photoId] = ($photoIdCounts[$photoId] ?? 0) + 1;
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
     * Property: Result count equals input count
     * 
     * The number of results SHALL equal the number of input photos,
     * ensuring no photos are duplicated or lost.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_result_count_equals_input_count(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(5, 100);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 2000 + $i;
                $faceCount = random_int(1, 10);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            $this->assertCount(
                $photoCount,
                $results,
                "Iteration {$iteration}: Expected {$photoCount} results, got " . count($results)
            );
        }
    }

    /**
     * Property: All input photo IDs appear in results
     * 
     * Every photo ID from the input collection SHALL appear exactly once
     * in the results.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_all_input_ids_in_results(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $inputPhotoIds = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 3000 + $i;
                $inputPhotoIds[] = $photoId;
                $faceCount = random_int(1, 5);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Extract result photo IDs
            $resultPhotoIds = array_map(fn($r) => $r->photoId, $results);

            // Verify all input IDs are in results
            $this->assertEqualsCanonicalizing(
                $inputPhotoIds,
                $resultPhotoIds,
                "Iteration {$iteration}: Not all input photo IDs appear in results"
            );
        }
    }

    /**
     * Property: Uniqueness holds with string photo IDs
     * 
     * Photo ID uniqueness SHALL work correctly with both integer and string IDs.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_uniqueness_with_string_ids(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $photoCount = random_int(10, 30);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $inputPhotoIds = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 'photo_' . (4000 + $i);
                $inputPhotoIds[] = $photoId;
                $faceCount = random_int(1, 5);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Count occurrences
            $photoIdCounts = [];
            foreach ($results as $result) {
                $photoId = $result->photoId;
                $photoIdCounts[$photoId] = ($photoIdCounts[$photoId] ?? 0) + 1;
            }

            // Verify uniqueness
            foreach ($photoIdCounts as $photoId => $count) {
                $this->assertEquals(
                    1,
                    $count,
                    "Iteration {$iteration}: String photo ID {$photoId} appears {$count} times"
                );
            }
        }
    }

    /**
     * Property: Uniqueness holds with large batches
     * 
     * Photo ID uniqueness SHALL be maintained even with large batches
     * (500+ photos).
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_uniqueness_large_batch(): void
    {
        for ($iteration = 1; $iteration <= 5; $iteration++) {
            $photoCount = random_int(500, 1000);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $inputPhotoIds = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 5000 + $i;
                $inputPhotoIds[] = $photoId;
                $faceCount = random_int(1, 3);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify count
            $this->assertCount(
                $photoCount,
                $results,
                "Iteration {$iteration}: Large batch lost photos"
            );

            // Verify all IDs present
            $resultPhotoIds = array_map(fn($r) => $r->photoId, $results);
            $this->assertEqualsCanonicalizing(
                $inputPhotoIds,
                $resultPhotoIds,
                "Iteration {$iteration}: Large batch has missing or duplicate IDs"
            );
        }
    }

    /**
     * Property: Uniqueness with photos having many faces
     * 
     * Even when photos contain many faces (10+), each photo SHALL appear
     * only once in results.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_uniqueness_with_many_faces_per_photo(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $photoCount = random_int(5, 20);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $inputPhotoIds = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 6000 + $i;
                $inputPhotoIds[] = $photoId;
                // Each photo has many faces
                $faceCount = random_int(10, 30);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Count occurrences
            $photoIdCounts = [];
            foreach ($results as $result) {
                $photoId = $result->photoId;
                $photoIdCounts[$photoId] = ($photoIdCounts[$photoId] ?? 0) + 1;
            }

            // Verify each appears exactly once
            foreach ($photoIdCounts as $photoId => $count) {
                $this->assertEquals(
                    1,
                    $count,
                    "Iteration {$iteration}: Photo {$photoId} with many faces appears {$count} times"
                );
            }
        }
    }

    /**
     * Property: Uniqueness is maintained across different thresholds
     * 
     * Photo ID uniqueness SHALL be maintained regardless of the threshold value.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_uniqueness_across_thresholds(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $photoCount = random_int(10, 30);
            $threshold = lcg_value();
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            $inputPhotoIds = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 7000 + $i;
                $inputPhotoIds[] = $photoId;
                $faceCount = random_int(1, 5);
                $photoEmbeddings[] = $this->createPhotoWithFaces($photoId, $faceCount);
            }

            // Process with specific threshold
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Count occurrences
            $photoIdCounts = [];
            foreach ($results as $result) {
                $photoId = $result->photoId;
                $photoIdCounts[$photoId] = ($photoIdCounts[$photoId] ?? 0) + 1;
            }

            // Verify uniqueness
            foreach ($photoIdCounts as $photoId => $count) {
                $this->assertEquals(
                    1,
                    $count,
                    "Iteration {$iteration}: Uniqueness violated with threshold {$threshold}"
                );
            }
        }
    }
}
