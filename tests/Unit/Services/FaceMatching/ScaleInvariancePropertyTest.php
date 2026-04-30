<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;

/**
 * Property-Based Test for Task 2.6: Scale Invariance
 *
 * **Property 5: Scale Invariance**
 * **Validates: Requirements 11.4**
 *
 * Verifies that cosine similarity is scale invariant:
 * For any two 128-dimensional embedding vectors A and B, and any positive scalar k:
 * similarity(A, B) = similarity(k×A, B)
 *
 * This is a fundamental mathematical property of cosine similarity.
 * Scaling a vector does not change its direction, so similarity remains the same.
 */
class ScaleInvariancePropertyTest extends TestCase
{
    private CosineSimilarityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CosineSimilarityCalculator();
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
     * Helper: Scale a vector by a positive scalar
     */
    private function scaleVector(array $vector, float $scalar): array
    {
        return array_map(fn($x) => $x * $scalar, $vector);
    }

    /**
     * Property: Cosine similarity is scale invariant
     * For any embeddings A and B, and positive scalar k: similarity(A, B) = similarity(k×A, B)
     * Runs 100 test cases with randomly generated embeddings and scalars.
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_with_random_embeddings(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            // Generate a random positive scalar
            $scalar = 0.1 + lcg_value() * 9.9; // Range: [0.1, 10.0]

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $embeddingB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated. " .
                "similarity(A, B) = {$similarityOriginal}, but similarity({$scalar}×A, B) = {$similarityScaled}"
            );
        }
    }

    /**
     * Property: Scale invariance holds when scaling B instead of A
     * For any embeddings A and B, and positive scalar k: similarity(A, B) = similarity(A, k×B)
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_scaling_second_vector(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            // Generate a random positive scalar
            $scalar = 0.1 + lcg_value() * 9.9; // Range: [0.1, 10.0]

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledB = $this->scaleVector($embeddingB, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($embeddingA, $scaledB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated when scaling B. " .
                "similarity(A, B) = {$similarityOriginal}, but similarity(A, {$scalar}×B) = {$similarityScaled}"
            );
        }
    }

    /**
     * Property: Scale invariance holds when scaling both vectors
     * For any embeddings A and B, and positive scalars k1, k2: similarity(A, B) = similarity(k1×A, k2×B)
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_scaling_both_vectors(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            // Generate random positive scalars
            $scalar1 = 0.1 + lcg_value() * 9.9; // Range: [0.1, 10.0]
            $scalar2 = 0.1 + lcg_value() * 9.9; // Range: [0.1, 10.0]

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar1);
            $scaledB = $this->scaleVector($embeddingB, $scalar2);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $scaledB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated when scaling both vectors."
            );
        }
    }

    /**
     * Property: Scale invariance holds with very small scalars
     * Tests scale invariance with very small positive scalars
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_very_small_scalars(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            // Generate a very small positive scalar
            $scalar = 1e-10 + lcg_value() * 1e-9; // Range: [1e-10, 1.1e-9]

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $embeddingB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated with very small scalars."
            );
        }
    }

    /**
     * Property: Scale invariance holds with very large scalars
     * Tests scale invariance with very large positive scalars
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_very_large_scalars(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            // Generate a very large positive scalar
            $scalar = 1e9 + lcg_value() * 1e9; // Range: [1e9, 2e9]

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $embeddingB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated with very large scalars."
            );
        }
    }

    /**
     * Property: Scale invariance holds with all positive values
     * Tests scale invariance with embeddings containing only positive values
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_all_positive_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(0.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(0.0, 1.0);

            $scalar = 0.1 + lcg_value() * 9.9;

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $embeddingB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated with all-positive values."
            );
        }
    }

    /**
     * Property: Scale invariance holds with mixed sign values
     * Tests scale invariance with embeddings containing mixed positive and negative values
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_mixed_sign_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(-1.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(-1.0, 1.0);

            $scalar = 0.1 + lcg_value() * 9.9;

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $embeddingB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated with mixed-sign values."
            );
        }
    }

    /**
     * Property: Scale invariance holds with unit vectors
     * Tests scale invariance with normalized embeddings (unit vectors)
     *
     * **Validates: Requirements 11.4**
     */
    public function test_property_scale_invariance_unit_vectors(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            // Generate random vectors and normalize them to unit vectors
            $rawA = $this->generateRandomEmbedding();
            $magA = sqrt(array_sum(array_map(fn($x) => $x ** 2, $rawA)));
            $embeddingA = array_map(fn($x) => $magA > 0 ? $x / $magA : 0, $rawA);

            $rawB = $this->generateRandomEmbedding();
            $magB = sqrt(array_sum(array_map(fn($x) => $x ** 2, $rawB)));
            $embeddingB = array_map(fn($x) => $magB > 0 ? $x / $magB : 0, $rawB);

            $scalar = 0.1 + lcg_value() * 9.9;

            $similarityOriginal = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $scaledA = $this->scaleVector($embeddingA, $scalar);
            $similarityScaled = $this->calculator->calculateSimilarity($scaledA, $embeddingB);

            $this->assertEqualsWithDelta(
                $similarityOriginal,
                $similarityScaled,
                1e-10,
                "Iteration {$iteration}: Scale invariance violated with unit vectors."
            );
        }
    }
}
