<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;

/**
 * Property-Based Test for Task 2.3: Similarity Score Range Validation
 *
 * **Property 2: Similarity Score Range Validation**
 * **Validates: Requirements 1.5, 1.6, 2.3, 2.4**
 *
 * Verifies that all cosine similarity scores are:
 * - Within the valid range [-1, 1]
 * - Finite (not NaN or Infinity)
 * - Numeric values
 *
 * For any two 128-dimensional embedding vectors, the cosine similarity
 * must always be a finite number in the range [-1, 1].
 */
class SimilarityScoreRangePropertyTest extends TestCase
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
     * Property: All similarity scores are in range [-1, 1]
     * Runs 100 test cases with randomly generated embeddings and verifies
     * that all results are within the valid range.
     *
     * **Validates: Requirements 1.5, 2.3**
     */
    public function test_property_similarity_score_in_valid_range(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            $similarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            $this->assertGreaterThanOrEqual(
                -1.0,
                $similarity,
                "Iteration {$iteration}: Similarity score {$similarity} is less than -1.0"
            );

            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Iteration {$iteration}: Similarity score {$similarity} is greater than 1.0"
            );
        }
    }

    /**
     * Property: All similarity scores are finite (not NaN or Infinity)
     * Verifies that results are valid numeric values
     *
     * **Validates: Requirements 1.6, 2.4**
     */
    public function test_property_similarity_score_is_finite(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            $similarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            $this->assertTrue(
                is_finite($similarity),
                "Iteration {$iteration}: Similarity score {$similarity} is not finite (NaN or Infinity)"
            );

            $this->assertFalse(
                is_nan($similarity),
                "Iteration {$iteration}: Similarity score is NaN"
            );

            $this->assertFalse(
                is_infinite($similarity),
                "Iteration {$iteration}: Similarity score is Infinity"
            );
        }
    }

    /**
     * Property: Similarity scores are numeric values
     * Verifies that results are valid numbers
     *
     * **Validates: Requirements 1.6**
     */
    public function test_property_similarity_score_is_numeric(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            $similarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            $this->assertTrue(
                is_numeric($similarity),
                "Iteration {$iteration}: Similarity score is not numeric"
            );

            $this->assertTrue(
                is_float($similarity) || is_int($similarity),
                "Iteration {$iteration}: Similarity score is not a float or int"
            );
        }
    }

    /**
     * Property: Range validation with extreme values
     * Tests with embeddings containing very large and very small values
     *
     * **Validates: Requirements 1.5, 1.6, 2.3, 2.4**
     */
    public function test_property_similarity_score_range_with_extreme_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            // Generate embeddings with extreme values
            $embeddingA = [];
            $embeddingB = [];
            for ($i = 0; $i < 128; $i++) {
                // Mix of very small and very large values
                if (rand(0, 1) === 0) {
                    $embeddingA[] = (lcg_value() - 0.5) * 1e-10;
                    $embeddingB[] = (lcg_value() - 0.5) * 1e-10;
                } else {
                    $embeddingA[] = (lcg_value() - 0.5) * 1e10;
                    $embeddingB[] = (lcg_value() - 0.5) * 1e10;
                }
            }

            $similarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            $this->assertGreaterThanOrEqual(
                -1.0,
                $similarity,
                "Iteration {$iteration}: Similarity with extreme values is less than -1.0"
            );

            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Iteration {$iteration}: Similarity with extreme values is greater than 1.0"
            );

            $this->assertTrue(
                is_finite($similarity),
                "Iteration {$iteration}: Similarity with extreme values is not finite"
            );
        }
    }

    /**
     * Property: Range validation with all positive values
     * Tests that range is maintained with all-positive embeddings
     *
     * **Validates: Requirements 1.5, 1.6**
     */
    public function test_property_similarity_score_range_all_positive(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(0.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(0.0, 1.0);

            $similarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            $this->assertGreaterThanOrEqual(
                -1.0,
                $similarity,
                "Iteration {$iteration}: All-positive similarity is less than -1.0"
            );

            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Iteration {$iteration}: All-positive similarity is greater than 1.0"
            );

            $this->assertTrue(
                is_finite($similarity),
                "Iteration {$iteration}: All-positive similarity is not finite"
            );
        }
    }

    /**
     * Property: Range validation with all negative values
     * Tests that range is maintained with all-negative embeddings
     *
     * **Validates: Requirements 1.5, 1.6**
     */
    public function test_property_similarity_score_range_all_negative(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(-1.0, 0.0);
            $embeddingB = $this->generateRandomEmbedding(-1.0, 0.0);

            $similarity = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);

            $this->assertGreaterThanOrEqual(
                -1.0,
                $similarity,
                "Iteration {$iteration}: All-negative similarity is less than -1.0"
            );

            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Iteration {$iteration}: All-negative similarity is greater than 1.0"
            );

            $this->assertTrue(
                is_finite($similarity),
                "Iteration {$iteration}: All-negative similarity is not finite"
            );
        }
    }
}
