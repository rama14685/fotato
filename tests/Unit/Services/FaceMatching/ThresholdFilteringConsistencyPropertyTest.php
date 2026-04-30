<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;

/**
 * Property-Based Test for Task 5.3: Threshold Filtering Consistency
 *
 * **Property 8: Threshold Filtering Consistency**
 * **Validates: Requirements 4.5**
 *
 * Verifies that photos are included in results if and only if their similarity
 * score is greater than or equal to the threshold. The filtering logic must be
 * consistent and deterministic.
 *
 * For any photo with similarity score S and threshold T:
 * - IF S >= T, THEN photo SHALL be included in results with matchesThreshold = true
 * - IF S < T, THEN photo SHALL be included in results with matchesThreshold = false
 * - The matchesThreshold flag SHALL accurately reflect the comparison
 */
class ThresholdFilteringConsistencyPropertyTest extends TestCase
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
     * Property: matchesThreshold flag is accurate for all photos
     * 
     * For every photo in results, the matchesThreshold flag SHALL correctly
     * indicate whether the similarity score meets or exceeds the threshold.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_property_matches_threshold_flag_accuracy(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            $threshold = lcg_value(); // Random threshold between 0 and 1
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            // Create photo embeddings
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 1000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            // Process with specific threshold
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Verify matchesThreshold flag for each result
            foreach ($results as $result) {
                $expectedMatches = $result->similarityScore >= $threshold;
                $this->assertEquals(
                    $expectedMatches,
                    $result->matchesThreshold,
                    "Iteration {$iteration}: Photo {$result->photoId} has similarity " .
                    "{$result->similarityScore} and threshold {$threshold}. " .
                    "matchesThreshold should be {$expectedMatches}, got {$result->matchesThreshold}"
                );
            }
        }
    }

    /**
     * Property: All results have similarity >= threshold when matchesThreshold is true
     * 
     * For every result with matchesThreshold = true, the similarity score
     * SHALL be greater than or equal to the threshold.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_property_matching_results_exceed_threshold(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            $threshold = lcg_value();
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 2000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Check all matching results
            foreach ($results as $result) {
                if ($result->matchesThreshold) {
                    $this->assertGreaterThanOrEqual(
                        $threshold,
                        $result->similarityScore,
                        "Iteration {$iteration}: Photo {$result->photoId} marked as matching " .
                        "but similarity {$result->similarityScore} < threshold {$threshold}"
                    );
                }
            }
        }
    }

    /**
     * Property: All results have similarity < threshold when matchesThreshold is false
     * 
     * For every result with matchesThreshold = false, the similarity score
     * SHALL be less than the threshold.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_property_non_matching_results_below_threshold(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            $threshold = lcg_value();
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 3000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Check all non-matching results
            foreach ($results as $result) {
                if (!$result->matchesThreshold) {
                    $this->assertLessThan(
                        $threshold,
                        $result->similarityScore,
                        "Iteration {$iteration}: Photo {$result->photoId} marked as non-matching " .
                        "but similarity {$result->similarityScore} >= threshold {$threshold}"
                    );
                }
            }
        }
    }

    /**
     * Property: Threshold filtering is consistent across different thresholds
     * 
     * For the same photo collection, changing the threshold should only affect
     * which photos have matchesThreshold = true/false, not the similarity scores.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_property_threshold_filtering_consistency(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $photoCount = random_int(10, 30);
            $threshold1 = lcg_value() * 0.5; // Lower threshold
            $threshold2 = 0.5 + lcg_value() * 0.5; // Higher threshold
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 4000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            // Process with both thresholds
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold1);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold2);

            // Create maps for easy lookup
            $map1 = [];
            foreach ($results1 as $result) {
                $map1[$result->photoId] = $result;
            }

            $map2 = [];
            foreach ($results2 as $result) {
                $map2[$result->photoId] = $result;
            }

            // Verify similarity scores are identical
            foreach ($map1 as $photoId => $result1) {
                $this->assertArrayHasKey($photoId, $map2);
                $result2 = $map2[$photoId];
                
                $this->assertEqualsWithDelta(
                    $result1->similarityScore,
                    $result2->similarityScore,
                    1e-10,
                    "Iteration {$iteration}: Similarity score for photo {$photoId} " .
                    "differs between thresholds"
                );
            }
        }
    }

    /**
     * Property: Boundary threshold values work correctly
     * 
     * Threshold values at boundaries (0.0, 1.0) and near boundaries should
     * work correctly with proper filtering.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_property_boundary_threshold_values(): void
    {
        $photoCount = 20;
        $customerEmbedding = $this->generateRandomEmbedding();
        $photoEmbeddings = [];

        for ($i = 0; $i < $photoCount; $i++) {
            $photoId = 5000 + $i;
            $photoEmbeddings[] = $this->createPhotoEmbedding(
                $photoId,
                $this->generateRandomEmbedding()
            );
        }

        // Test with threshold = 0.0 (all non-negative scores should match)
        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.0);
        foreach ($results as $result) {
            if ($result->similarityScore >= 0.0) {
                $this->assertTrue(
                    $result->matchesThreshold,
                    "With threshold 0.0, photos with similarity >= 0.0 should match"
                );
            }
        }

        // Test with threshold = 1.0 (only perfect matches)
        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 1.0);
        foreach ($results as $result) {
            // Only identical embeddings would have similarity = 1.0
            if ($result->matchesThreshold) {
                $this->assertEqualsWithDelta(
                    1.0,
                    $result->similarityScore,
                    1e-10,
                    "With threshold 1.0, only perfect matches should have matchesThreshold = true"
                );
            }
        }
    }
}
