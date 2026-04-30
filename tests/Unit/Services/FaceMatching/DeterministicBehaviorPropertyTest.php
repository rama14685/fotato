<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;

/**
 * Property-Based Test for Task 11.2: Deterministic Behavior
 *
 * **Property 15: Deterministic Behavior**
 * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
 *
 * Verifies that for any identical inputs (customer embedding, photo embeddings, threshold),
 * multiple calls to the Face Matching Service SHALL return identical results in identical order.
 *
 * This property ensures:
 * - Calculating similarity(A, B) twice returns identical values (Req 12.1)
 * - The calculator is deterministic with no randomness (Req 12.2)
 * - Calling the service multiple times with same inputs produces same results (Req 12.4)
 * - Result ordering is stable - same similarity scores maintain consistent order (Req 12.5)
 */
class DeterministicBehaviorPropertyTest extends TestCase
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
     * Helper: Create PhotoEmbeddingData with multiple faces
     */
    private function createPhotoEmbeddingMultipleFaces(int|string $photoId, array $embeddings): PhotoEmbeddingData
    {
        return new PhotoEmbeddingData($photoId, $embeddings);
    }

    /**
     * Helper: Assert two result arrays are identical
     */
    private function assertResultsIdentical(array $results1, array $results2, string $context): void
    {
        $this->assertCount(
            count($results1),
            $results2,
            "{$context}: Result count differs"
        );

        for ($i = 0; $i < count($results1); $i++) {
            $this->assertEquals(
                $results1[$i]->photoId,
                $results2[$i]->photoId,
                "{$context}: Photo ID differs at position {$i}"
            );

            $this->assertEqualsWithDelta(
                $results1[$i]->similarityScore,
                $results2[$i]->similarityScore,
                1e-15,
                "{$context}: Similarity score differs at position {$i}"
            );

            $this->assertEquals(
                $results1[$i]->matchesThreshold,
                $results2[$i]->matchesThreshold,
                "{$context}: Threshold match status differs at position {$i}"
            );
        }
    }

    /**
     * Property: Multiple calls with identical inputs return identical results
     * 
     * For any identical inputs (customer embedding, photo embeddings, threshold),
     * multiple calls SHALL return identical results in identical order.
     *
     * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
     */
    public function test_property_deterministic_behavior_single_photo(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [
                $this->createPhotoEmbedding(1001, $this->generateRandomEmbedding())
            ];
            $threshold = 0.5 + (lcg_value() * 0.4); // Random threshold between 0.5 and 0.9

            // Call service multiple times with identical inputs
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Verify all results are identical
            $this->assertResultsIdentical($results1, $results2, "Iteration {$iteration}, Run 1 vs 2");
            $this->assertResultsIdentical($results1, $results3, "Iteration {$iteration}, Run 1 vs 3");
        }
    }

    /**
     * Property: Deterministic behavior with multiple photos
     * 
     * For any collection of multiple photos, multiple calls SHALL return
     * identical results in identical order.
     *
     * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
     */
    public function test_property_deterministic_behavior_multiple_photos(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $photoCount = random_int(5, 50);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 2000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            $threshold = 0.5 + (lcg_value() * 0.4);

            // Call service multiple times
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Verify all results are identical
            $this->assertResultsIdentical($results1, $results2, "Iteration {$iteration}, Run 1 vs 2");
            $this->assertResultsIdentical($results1, $results3, "Iteration {$iteration}, Run 1 vs 3");
        }
    }

    /**
     * Property: Deterministic behavior with multiple faces per photo
     * 
     * For photos containing multiple faces, multiple calls SHALL return
     * identical results (same maximum similarity score selected).
     *
     * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
     */
    public function test_property_deterministic_behavior_multiple_faces_per_photo(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $photoCount = random_int(3, 20);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 3000 + $i;
                $faceCount = random_int(1, 5);
                $faces = [];
                
                for ($j = 0; $j < $faceCount; $j++) {
                    $faces[] = $this->generateRandomEmbedding();
                }
                
                $photoEmbeddings[] = $this->createPhotoEmbeddingMultipleFaces($photoId, $faces);
            }

            $threshold = 0.5 + (lcg_value() * 0.4);

            // Call service multiple times
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Verify all results are identical
            $this->assertResultsIdentical($results1, $results2, "Iteration {$iteration}, Run 1 vs 2");
            $this->assertResultsIdentical($results1, $results3, "Iteration {$iteration}, Run 1 vs 3");
        }
    }

    /**
     * Property: Deterministic behavior with default threshold
     * 
     * When using default threshold (no explicit threshold parameter),
     * multiple calls SHALL return identical results.
     *
     * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
     */
    public function test_property_deterministic_behavior_default_threshold(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $photoCount = random_int(5, 30);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 4000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            // Call service multiple times with default threshold (null)
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

            // Verify all results are identical
            $this->assertResultsIdentical($results1, $results2, "Iteration {$iteration}, Run 1 vs 2");
            $this->assertResultsIdentical($results1, $results3, "Iteration {$iteration}, Run 1 vs 3");
        }
    }

    /**
     * Property: Cosine similarity calculation is deterministic
     * 
     * Calculating similarity(A, B) multiple times SHALL return identical values.
     * This tests the calculator directly to ensure no randomness in calculations.
     *
     * **Validates: Requirements 12.1, 12.2**
     */
    public function test_property_cosine_similarity_calculation_deterministic(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            // Calculate similarity multiple times
            $similarity1 = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarity2 = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarity3 = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            // Verify all calculations return identical values
            $this->assertEqualsWithDelta(
                $similarity1,
                $similarity2,
                1e-15,
                "Iteration {$iteration}: Calculation 1 and 2 differ"
            );

            $this->assertEqualsWithDelta(
                $similarity1,
                $similarity3,
                1e-15,
                "Iteration {$iteration}: Calculation 1 and 3 differ"
            );
        }
    }

    /**
     * Property: Deterministic behavior with edge case embeddings
     * 
     * Even with edge case embeddings (all positive, all negative, mixed),
     * multiple calls SHALL return identical results.
     *
     * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
     */
    public function test_property_deterministic_behavior_edge_cases(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $photoCount = random_int(5, 20);
            
            // Generate edge case embeddings
            $customerEmbedding = match ($iteration % 3) {
                0 => $this->generateRandomEmbedding(0.0, 1.0),  // All positive
                1 => $this->generateRandomEmbedding(-1.0, 0.0), // All negative
                default => $this->generateRandomEmbedding(-1.0, 1.0), // Mixed
            };

            $photoEmbeddings = [];
            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 5000 + $i;
                $photoEmbedding = match (($iteration + $i) % 3) {
                    0 => $this->generateRandomEmbedding(0.0, 1.0),
                    1 => $this->generateRandomEmbedding(-1.0, 0.0),
                    default => $this->generateRandomEmbedding(-1.0, 1.0),
                };
                $photoEmbeddings[] = $this->createPhotoEmbedding($photoId, $photoEmbedding);
            }

            $threshold = 0.5 + (lcg_value() * 0.4);

            // Call service multiple times
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Verify all results are identical
            $this->assertResultsIdentical($results1, $results2, "Iteration {$iteration}, Run 1 vs 2");
            $this->assertResultsIdentical($results1, $results3, "Iteration {$iteration}, Run 1 vs 3");
        }
    }

    /**
     * Property: Result ordering is stable across multiple calls
     * 
     * The order of results SHALL be consistent across multiple calls with
     * identical inputs, even when similarity scores are very close.
     *
     * **Validates: Requirements 12.5**
     */
    public function test_property_result_ordering_stable(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $photoCount = random_int(10, 40);
            
            $customerEmbedding = $this->generateRandomEmbedding();
            $photoEmbeddings = [];

            for ($i = 0; $i < $photoCount; $i++) {
                $photoId = 6000 + $i;
                $photoEmbeddings[] = $this->createPhotoEmbedding(
                    $photoId,
                    $this->generateRandomEmbedding()
                );
            }

            $threshold = 0.5 + (lcg_value() * 0.4);

            // Call service multiple times
            $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
            $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Verify photo ID order is identical across all runs
            $photoIds1 = array_map(fn($r) => $r->photoId, $results1);
            $photoIds2 = array_map(fn($r) => $r->photoId, $results2);
            $photoIds3 = array_map(fn($r) => $r->photoId, $results3);

            $this->assertEquals(
                $photoIds1,
                $photoIds2,
                "Iteration {$iteration}: Photo ID order differs between run 1 and 2"
            );

            $this->assertEquals(
                $photoIds1,
                $photoIds3,
                "Iteration {$iteration}: Photo ID order differs between run 1 and 3"
            );
        }
    }

    /**
     * Property: Deterministic behavior with varying threshold values
     * 
     * For any specific threshold value, multiple calls SHALL return
     * identical results. Tests various threshold values.
     *
     * **Validates: Requirements 12.1, 12.2, 12.4, 12.5**
     */
    public function test_property_deterministic_behavior_varying_thresholds(): void
    {
        $thresholds = [0.0, 0.3, 0.5, 0.6, 0.7, 0.9, 1.0];

        foreach ($thresholds as $threshold) {
            for ($iteration = 1; $iteration <= 15; $iteration++) {
                $photoCount = random_int(5, 30);
                
                $customerEmbedding = $this->generateRandomEmbedding();
                $photoEmbeddings = [];

                for ($i = 0; $i < $photoCount; $i++) {
                    $photoId = 7000 + ($iteration * 100) + $i;
                    $photoEmbeddings[] = $this->createPhotoEmbedding(
                        $photoId,
                        $this->generateRandomEmbedding()
                    );
                }

                // Call service multiple times with specific threshold
                $results1 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
                $results2 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
                $results3 = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

                // Verify all results are identical
                $this->assertResultsIdentical(
                    $results1,
                    $results2,
                    "Threshold {$threshold}, Iteration {$iteration}, Run 1 vs 2"
                );
                $this->assertResultsIdentical(
                    $results1,
                    $results3,
                    "Threshold {$threshold}, Iteration {$iteration}, Run 1 vs 3"
                );
            }
        }
    }
}
