<?php

namespace Tests\Unit;

use App\Http\Controllers\FaceScanController;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Property-Based Test for Task 13.2: Cosine Similarity Range
 *
 * **Property 10: Cosine Similarity Range**
 * **Validates: Requirements 5.6**
 *
 * For any two valid embedding vectors, the cosine similarity
 * SHALL be in the range [-1, 1].
 */
class CosineSimilarityRangePropertyTest extends TestCase
{
    private ReflectionMethod $method;
    private FaceScanController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new FaceScanController();
        $this->method = new ReflectionMethod($this->controller, 'cosineSimilarity');
        $this->method->setAccessible(true);
    }

    /**
     * Helper: invoke cosineSimilarity via reflection.
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        return $this->method->invoke($this->controller, $a, $b);
    }

    /**
     * Helper: generate a random float vector of given length.
     */
    private function randomVector(int $length, float $min = -1.0, float $max = 1.0): array
    {
        $vec = [];
        for ($i = 0; $i < $length; $i++) {
            $vec[] = $min + lcg_value() * ($max - $min);
        }
        return $vec;
    }

    /**
     * Property: similarity is always in [-1, 1] for random 128-dimensional vectors.
     *
     * **Validates: Requirements 5.6**
     */
    public function test_property_range_with_128_dimensional_vectors(): void
    {
        for ($scenario = 1; $scenario <= 100; $scenario++) {
            $a = $this->randomVector(128);
            $b = $this->randomVector(128);

            $similarity = $this->cosineSimilarity($a, $b);

            $this->assertGreaterThanOrEqual(
                -1.0,
                $similarity,
                "Scenario {$scenario}: similarity {$similarity} is below -1."
            );
            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Scenario {$scenario}: similarity {$similarity} is above 1."
            );
        }
    }

    /**
     * Property: range holds for vectors of various lengths.
     *
     * **Validates: Requirements 5.6**
     */
    public function test_property_range_with_various_vector_lengths(): void
    {
        $lengths = [1, 2, 5, 10, 32, 64, 128, 256];

        foreach ($lengths as $length) {
            for ($scenario = 1; $scenario <= 10; $scenario++) {
                $a = $this->randomVector($length);
                $b = $this->randomVector($length);

                $similarity = $this->cosineSimilarity($a, $b);

                $this->assertGreaterThanOrEqual(
                    -1.0,
                    $similarity,
                    "Length {$length}, Scenario {$scenario}: similarity {$similarity} is below -1."
                );
                $this->assertLessThanOrEqual(
                    1.0,
                    $similarity,
                    "Length {$length}, Scenario {$scenario}: similarity {$similarity} is above 1."
                );
            }
        }
    }

    /**
     * Property: range holds for all-positive vectors.
     *
     * **Validates: Requirements 5.6**
     */
    public function test_property_range_with_all_positive_vectors(): void
    {
        for ($scenario = 1; $scenario <= 30; $scenario++) {
            $length = rand(1, 128);
            $a = $this->randomVector($length, 0.0, 10.0);
            $b = $this->randomVector($length, 0.0, 10.0);

            $similarity = $this->cosineSimilarity($a, $b);

            // All-positive vectors should yield similarity in [0, 1]
            $this->assertGreaterThanOrEqual(
                0.0,
                $similarity,
                "Scenario {$scenario}: all-positive vectors should yield non-negative similarity."
            );
            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Scenario {$scenario}: similarity {$similarity} is above 1."
            );
        }
    }

    /**
     * Property: range holds for all-negative vectors.
     *
     * **Validates: Requirements 5.6**
     */
    public function test_property_range_with_all_negative_vectors(): void
    {
        for ($scenario = 1; $scenario <= 30; $scenario++) {
            $length = rand(1, 128);
            $a = $this->randomVector($length, -10.0, 0.0);
            $b = $this->randomVector($length, -10.0, 0.0);

            $similarity = $this->cosineSimilarity($a, $b);

            // All-negative vectors should yield similarity in [0, 1]
            $this->assertGreaterThanOrEqual(
                0.0,
                $similarity,
                "Scenario {$scenario}: all-negative vectors should yield non-negative similarity."
            );
            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Scenario {$scenario}: similarity {$similarity} is above 1."
            );
        }
    }

    /**
     * Property: range holds for large-magnitude vectors.
     *
     * **Validates: Requirements 5.6**
     */
    public function test_property_range_with_large_magnitude_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(10, 128);
            $a = $this->randomVector($length, -1000.0, 1000.0);
            $b = $this->randomVector($length, -1000.0, 1000.0);

            $similarity = $this->cosineSimilarity($a, $b);

            $this->assertGreaterThanOrEqual(
                -1.0,
                $similarity,
                "Scenario {$scenario}: large-magnitude similarity {$similarity} is below -1."
            );
            $this->assertLessThanOrEqual(
                1.0,
                $similarity,
                "Scenario {$scenario}: large-magnitude similarity {$similarity} is above 1."
            );
        }
    }
}
