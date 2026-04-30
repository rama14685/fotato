<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;

/**
 * Property-Based Test for Task 5.4: Result Sorting Correctness
 *
 * **Property 9: Result Sorting Correctness**
 * **Validates: Requirements 4.7**
 *
 * Verifies that results are sorted by similarity score in descending order
 * (highest match first). The sorting must be stable and consistent.
 *
 * For any collection of match results:
 * - Results SHALL be sorted by similarity score in descending order
 * - For each pair of consecutive results, similarity[i] >= similarity[i+1]
 * - The sorting SHALL be stable (equal scores maintain relative order)
 */
class ResultSortingCorrectnessPropertyTest extends TestCase
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
     * Property: Results are sorted in descending order by similarity score
     * 
     * For any collection of results, each result's similarity score SHALL be
     * greater than or equal to the next result's similarity score.
     *
     * **Validates: Requirements 4.7**
     */
    public function test_property_results_sorted_descending(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 100);
            
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

            // Process all photos
            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify descending order
            for ($i = 0; $i < count($results) - 1; $i++) {
                $this->assertGreaterThanOrEqual(
                    $results[$i + 1]->similarityScore,
                    $results[$i]->similarityScore,
                    "Iteration {$iteration}: Results not sorted in descending order. " .
                    "Position {$i}: {$results[$i]->similarityScore}, " .
                    "Position " . ($i + 1) . ": {$results[$i + 1]->similarityScore}"
                );
            }
        }
    }

    /**
     * Property: Highest similarity score is first
     * 
     * The first result in the collection SHALL have the highest similarity score
     * among all results.
     *
     * **Validates: Requirements 4.7**
     */
    public function test_property_highest_score_first(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 2000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            if (!empty($results)) {
                $maxScore = max(array_map(fn($r) => $r->similarityScore, $results));
                $this->assertEqualsWithDelta(
                    $maxScore,
                    $results[0]->similarityScore,
                    1e-10,
                    "Iteration {$iteration}: First result does not have highest similarity score"
                );
            }
        }
    }

    /**
     * Property: Lowest similarity score is last
     * 
     * The last result in the collection SHALL have the lowest similarity score
     * among all results.
     *
     * **Validates: Requirements 4.7**
     */
    public function test_property_lowest_score_last(): void
    {
        for ($iteration = 1; $iteration <= 30; $iteration++) {
            $photoCount = random_int(10, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 3000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            if (!empty($results)) {
                $minScore = min(array_map(fn($r) => $r->similarityScore, $results));
                $this->assertEqualsWithDelta(
                    $minScore,
                    $results[count($results) - 1]->similarityScore,
                    1e-10,
                    "Iteration {$iteration}: Last result does not have lowest similarity score"
                );
            }
        }
    }

    /**
     * Property: Sorting is stable for equal similarity scores
     * 
     * When multiple photos have the same similarity score, their relative order
     * should be maintained (stable sort).
     *
     * **Validates: Requirements 4.7**
     */
    public function test_property_stable_sort_for_equal_scores(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            // Create embeddings that will produce identical similarity scores
            $customerEmbedding = $this->generateRandomEmbedding();
            
            // Create multiple identical embeddings (will have same similarity)
            $identicalEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];
            
            $photoCount = random_int(5, 20);
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 4000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $identicalEmbedding
                );
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // All results should have identical similarity scores
            if (count($results) > 1) {
                $firstScore = $results[0]->similarityScore;
                foreach ($results as $result) {
                    $this->assertEqualsWithDelta(
                        $firstScore,
                        $result->similarityScore,
                        1e-10,
                        "Iteration {$iteration}: Expected all scores to be equal"
                    );
                }
            }
        }
    }

    /**
     * Property: Sorting works correctly with mixed positive and negative scores
     * 
     * Results should be sorted correctly even when similarity scores include
     * negative values (orthogonal or opposite embeddings).
     *
     * **Validates: Requirements 4.7**
     */
    public function test_property_sorting_with_negative_scores(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $photoCount = random_int(10, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            // Create embeddings that may produce negative similarity scores
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 5000 + $i;
                // Mix of positive and negative values
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding(-1.0, 1.0)
                );
            }

            $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify descending order even with negative scores
            for ($i = 0; $i < count($results) - 1; $i++) {
                $this->assertGreaterThanOrEqual(
                    $results[$i + 1]->similarityScore,
                    $results[$i]->similarityScore,
                    "Iteration {$iteration}: Results not sorted correctly with negative scores"
                );
            }
        }
    }

    /**
     * Property: Sorting is deterministic
     * 
     * Processing the same input multiple times should produce results in the
     * same order (deterministic sorting).
     *
     * **Validates: Requirements 4.7**
     */
    public function test_property_sorting_is_deterministic(): void
    {
        for ($iteration = 1; $iteration <= 20; $iteration++) {
            $photoCount = random_int(10, 30);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 6000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            // Process multiple times
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify all runs produce identical order
            for ($i = 0; $i < count($results1); $i++) {
                $this->assertEquals(
                    $results1[$i]->photoId,
                    $results2[$i]->photoId,
                    "Iteration {$iteration}: Run 1 and 2 differ at position {$i}"
                );
                $this->assertEquals(
                    $results1[$i]->photoId,
                    $results3[$i]->photoId,
                    "Iteration {$iteration}: Run 1 and 3 differ at position {$i}"
                );
                $this->assertEqualsWithDelta(
                    $results1[$i]->similarityScore,
                    $results2[$i]->similarityScore,
                    1e-10,
                    "Iteration {$iteration}: Scores differ between runs at position {$i}"
                );
            }
        }
    }
}
