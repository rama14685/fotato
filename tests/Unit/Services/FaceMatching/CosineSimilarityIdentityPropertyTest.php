<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;

/**
 * Property-Based Test for Task 2.5: Cosine Similarity Identity
 *
 * **Property 4: Cosine Similarity Identity**
 * **Validates: Requirements 11.2**
 *
 * Verifies that cosine similarity satisfies the identity property:
 * For any 128-dimensional embedding vector A:
 * similarity(A, A) = 1.0 (within floating-point tolerance of 0.0001)
 *
 * This is a fundamental mathematical property of cosine similarity.
 * A vector is always perfectly similar to itself.
 */
class CosineSimilarityIdentityPropertyTest extends TestCase
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
     * Property: Cosine similarity of a vector with itself is 1.0
     * For any embedding A: similarity(A, A) = 1.0
     * Runs 100 test cases with randomly generated embeddings.
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_with_random_embeddings(): void
    {
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $embedding = $this->generateRandomEmbedding();

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity property violated. " .
                "similarity(A, A) should be 1.0, got {$similarity}"
            );
        }
    }

    /**
     * Property: Identity holds with all positive values
     * Tests identity with embeddings containing only positive values
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_all_positive_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embedding = $this->generateRandomEmbedding(0.0, 1.0);

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with all-positive values."
            );
        }
    }

    /**
     * Property: Identity holds with all negative values
     * Tests identity with embeddings containing only negative values
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_all_negative_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embedding = $this->generateRandomEmbedding(-1.0, 0.0);

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with all-negative values."
            );
        }
    }

    /**
     * Property: Identity holds with mixed sign values
     * Tests identity with embeddings containing mixed positive and negative values
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_mixed_sign_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embedding = $this->generateRandomEmbedding(-1.0, 1.0);

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with mixed-sign values."
            );
        }
    }

    /**
     * Property: Identity holds with extreme values
     * Tests identity with embeddings containing very large and very small values
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_extreme_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            // Generate embedding with extreme values
            $embedding = [];
            for ($i = 0; $i < 128; $i++) {
                if (rand(0, 1) === 0) {
                    $embedding[] = (lcg_value() - 0.5) * 1e-10;
                } else {
                    $embedding[] = (lcg_value() - 0.5) * 1e10;
                }
            }

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with extreme values."
            );
        }
    }

    /**
     * Property: Identity holds with unit vectors
     * Tests identity with normalized embeddings (unit vectors)
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_unit_vectors(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            // Generate random vector and normalize it to unit vector
            $raw = $this->generateRandomEmbedding();
            $mag = sqrt(array_sum(array_map(fn($x) => $x ** 2, $raw)));
            $embedding = array_map(fn($x) => $mag > 0 ? $x / $mag : 0, $raw);

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with unit vectors."
            );
        }
    }

    /**
     * Property: Identity holds with very small values
     * Tests identity with embeddings containing values very close to zero
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_very_small_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embedding = [];
            for ($i = 0; $i < 128; $i++) {
                $embedding[] = (lcg_value() - 0.5) * 1e-15;
            }

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with very small values."
            );
        }
    }

    /**
     * Property: Identity holds with very large values
     * Tests identity with embeddings containing very large values
     *
     * **Validates: Requirements 11.2**
     */
    public function test_property_identity_very_large_values(): void
    {
        for ($iteration = 1; $iteration <= 50; $iteration++) {
            $embedding = [];
            for ($i = 0; $i < 128; $i++) {
                $embedding[] = (lcg_value() - 0.5) * 1e15;
            }

            $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                0.0001,
                "Iteration {$iteration}: Identity violated with very large values."
            );
        }
    }
}
