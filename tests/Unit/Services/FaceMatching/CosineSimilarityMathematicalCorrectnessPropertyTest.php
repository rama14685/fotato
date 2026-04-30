<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;

/**
 * Property-Based Test for Task 2.2: Cosine Similarity Mathematical Correctness
 *
 * **Property 1: Cosine Similarity Mathematical Correctness**
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
 *
 * Verifies that the cosine similarity formula is correctly implemented:
 * similarity = dot_product(A, B) / (magnitude(A) × magnitude(B))
 *
 * For any two 128-dimensional embedding vectors A and B:
 * - The dot product is correctly computed as sum of element-wise multiplication
 * - The magnitude (L2 norm) is correctly computed as sqrt(sum of squared elements)
 * - The similarity is correctly calculated as dot_product / (magnitude_A × magnitude_B)
 */
class CosineSimilarityMathematicalCorrectnessPropertyTest extends TestCase
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
     * Helper: Manually compute cosine similarity using the mathematical formula
     * This serves as a reference implementation to verify the calculator
     */
    private function manualCosineSimilarity(array $a, array $b): float
    {
        // Compute dot product
        $dotProduct = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
        }

        // Compute magnitude of A
        $magnitudeA = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $magnitudeA += $a[$i] * $a[$i];
        }
        $magnitudeA = sqrt($magnitudeA);

        // Compute magnitude of B
        $magnitudeB = 0.0;
        for ($i = 0; $i < count($b); $i++) {
            $magnitudeB += $b[$i] * $b[$i];
        }
        $magnitudeB = sqrt($magnitudeB);

        // Handle zero magnitude edge case
        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Property: Cosine similarity formula is correctly implemented
     * for random 128-dimensional vectors
     *
     * Runs 100 test cases with randomly generated embeddings and verifies
     * that the calculator produces the same result as the manual formula.
     *
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     */
    public function test_property_cosine_similarity_formula_correctness(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            $calculatedSimilarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $expectedSimilarity = $this->manualCosineSimilarity($embeddingA, $embeddingB);

            $this->assertEqualsWithDelta(
                $expectedSimilarity,
                $calculatedSimilarity,
                1e-10,
                "Iteration {$iteration}: Cosine similarity formula not correctly implemented. " .
                "Expected {$expectedSimilarity}, got {$calculatedSimilarity}."
            );
        }
    }

    /**
     * Property: Dot product is correctly computed
     * Verifies that dotProduct method correctly sums element-wise multiplication
     *
     * **Validates: Requirements 1.1**
     */
    public function test_property_dot_product_correctness(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $vectorA = $this->generateRandomEmbedding();
            $vectorB = $this->generateRandomEmbedding();

            // Manual computation
            $expectedDotProduct = 0.0;
            for ($i = 0; $i < 128; $i++) {
                $expectedDotProduct += $vectorA[$i] * $vectorB[$i];
            }

            $calculatedDotProduct = $this->calculator->dotProduct($vectorA, $vectorB);

            $this->assertEqualsWithDelta(
                $expectedDotProduct,
                $calculatedDotProduct,
                1e-10,
                "Iteration {$iteration}: Dot product not correctly computed. " .
                "Expected {$expectedDotProduct}, got {$calculatedDotProduct}."
            );
        }
    }

    /**
     * Property: Magnitude (L2 norm) is correctly computed
     * Verifies that magnitude method correctly computes sqrt(sum of squared elements)
     *
     * **Validates: Requirements 1.2**
     */
    public function test_property_magnitude_correctness(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $vector = $this->generateRandomEmbedding();

            // Manual computation
            $sumOfSquares = 0.0;
            for ($i = 0; $i < 128; $i++) {
                $sumOfSquares += $vector[$i] * $vector[$i];
            }
            $expectedMagnitude = sqrt($sumOfSquares);

            $calculatedMagnitude = $this->calculator->magnitude($vector);

            $this->assertEqualsWithDelta(
                $expectedMagnitude,
                $calculatedMagnitude,
                1e-10,
                "Iteration {$iteration}: Magnitude not correctly computed. " .
                "Expected {$expectedMagnitude}, got {$calculatedMagnitude}."
            );
        }
    }

    /**
     * Property: Cosine similarity with all positive values
     * Verifies correctness with embeddings containing only positive values
     *
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     */
    public function test_property_cosine_similarity_all_positive_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(0.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(0.0, 1.0);

            $calculatedSimilarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $expectedSimilarity = $this->manualCosineSimilarity($embeddingA, $embeddingB);

            $this->assertEqualsWithDelta(
                $expectedSimilarity,
                $calculatedSimilarity,
                1e-10,
                "Iteration {$iteration}: Formula incorrect for all-positive vectors."
            );
        }
    }

    /**
     * Property: Cosine similarity with mixed positive and negative values
     * Verifies correctness with embeddings containing mixed signs
     *
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     */
    public function test_property_cosine_similarity_mixed_sign_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(-1.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(-1.0, 1.0);

            $calculatedSimilarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $expectedSimilarity = $this->manualCosineSimilarity($embeddingA, $embeddingB);

            $this->assertEqualsWithDelta(
                $expectedSimilarity,
                $calculatedSimilarity,
                1e-10,
                "Iteration {$iteration}: Formula incorrect for mixed-sign vectors."
            );
        }
    }
}
