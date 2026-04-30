<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;

/**
 * Property-Based Test for Task 2.4: Cosine Similarity Symmetry
 *
 * **Property 3: Cosine Similarity Symmetry**
 * **Validates: Requirements 11.1**
 *
 * Verifies that cosine similarity is symmetric:
 * For any two 128-dimensional embedding vectors A and B:
 * similarity(A, B) = similarity(B, A)
 *
 * This is a fundamental mathematical property of cosine similarity.
 */
class CosineSimilaritySymmetryPropertyTest extends TestCase
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
     * Property: Cosine similarity is symmetric
     * For any two embeddings A and B: similarity(A, B) = similarity(B, A)
     * Runs 100 test cases with randomly generated embeddings.
     *
     * **Validates: Requirements 11.1**
     */
    public function test_property_symmetry_with_random_embeddings(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding();
            $embeddingB = $this->generateRandomEmbedding();

            $similarityAB = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarityBA = $this->calculator->calculateSimilarity($embeddingB, $embeddingA);

            $this->assertEqualsWithDelta(
                $similarityAB,
                $similarityBA,
                1e-10,
                "Iteration {$iteration}: Symmetry violated. " .
                "similarity(A, B) = {$similarityAB}, but similarity(B, A) = {$similarityBA}"
            );
        }
    }

    /**
     * Property: Symmetry holds with all positive values
     * Tests symmetry with embeddings containing only positive values
     *
     * **Validates: Requirements 11.1**
     */
    public function test_property_symmetry_all_positive_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(0.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(0.0, 1.0);

            $similarityAB = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarityBA = $this->calculator->calculateSimilarity($embeddingB, $embeddingA);

            $this->assertEqualsWithDelta(
                $similarityAB,
                $similarityBA,
                1e-10,
                "Iteration {$iteration}: Symmetry violated with all-positive values."
            );
        }
    }

    /**
     * Property: Symmetry holds with all negative values
     * Tests symmetry with embeddings containing only negative values
     *
     * **Validates: Requirements 11.1**
     */
    public function test_property_symmetry_all_negative_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(-1.0, 0.0);
            $embeddingB = $this->generateRandomEmbedding(-1.0, 0.0);

            $similarityAB = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarityBA = $this->calculator->calculateSimilarity($embeddingB, $embeddingA);

            $this->assertEqualsWithDelta(
                $similarityAB,
                $similarityBA,
                1e-10,
                "Iteration {$iteration}: Symmetry violated with all-negative values."
            );
        }
    }

    /**
     * Property: Symmetry holds with mixed sign values
     * Tests symmetry with embeddings containing mixed positive and negative values
     *
     * **Validates: Requirements 11.1**
     */
    public function test_property_symmetry_mixed_sign_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embeddingA = $this->generateRandomEmbedding(-1.0, 1.0);
            $embeddingB = $this->generateRandomEmbedding(-1.0, 1.0);

            $similarityAB = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarityBA = $this->calculator->calculateSimilarity($embeddingB, $embeddingA);

            $this->assertEqualsWithDelta(
                $similarityAB,
                $similarityBA,
                1e-10,
                "Iteration {$iteration}: Symmetry violated with mixed-sign values."
            );
        }
    }

    /**
     * Property: Symmetry holds with extreme values
     * Tests symmetry with embeddings containing very large and very small values
     *
     * **Validates: Requirements 11.1**
     */
    public function test_property_symmetry_extreme_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            // Generate embeddings with extreme values
            $embeddingA = [];
            $embeddingB = [];
            for ($i = 0; $i < 128; $i++) {
                if (rand(0, 1) === 0) {
                    $embeddingA[] = (lcg_value() - 0.5) * 1e-10;
                    $embeddingB[] = (lcg_value() - 0.5) * 1e-10;
                } else {
                    $embeddingA[] = (lcg_value() - 0.5) * 1e10;
                    $embeddingB[] = (lcg_value() - 0.5) * 1e10;
                }
            }

            $similarityAB = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarityBA = $this->calculator->calculateSimilarity($embeddingB, $embeddingA);

            $this->assertEqualsWithDelta(
                $similarityAB,
                $similarityBA,
                1e-10,
                "Iteration {$iteration}: Symmetry violated with extreme values."
            );
        }
    }

    /**
     * Property: Symmetry holds with unit vectors
     * Tests symmetry with normalized embeddings (unit vectors)
     *
     * **Validates: Requirements 11.1**
     */
    public function test_property_symmetry_unit_vectors(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            // Generate random vectors and normalize them to unit vectors
            $rawA = $this->generateRandomEmbedding();
            $magA = sqrt(array_sum(array_map(fn($x) => $x ** 2, $rawA)));
            $embeddingA = array_map(fn($x) => $magA > 0 ? $x / $magA : 0, $rawA);

            $rawB = $this->generateRandomEmbedding();
            $magB = sqrt(array_sum(array_map(fn($x) => $x ** 2, $rawB)));
            $embeddingB = array_map(fn($x) => $magB > 0 ? $x / $magB : 0, $rawB);

            $similarityAB = $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
            $similarityBA = $this->calculator->calculateSimilarity($embeddingB, $embeddingA);

            $this->assertEqualsWithDelta(
                $similarityAB,
                $similarityBA,
                1e-10,
                "Iteration {$iteration}: Symmetry violated with unit vectors."
            );
        }
    }
}
