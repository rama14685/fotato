<?php

namespace Tests\Unit;

use App\Http\Controllers\FaceScanController;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Property-Based Test for Task 13.4: Zero Vector Handling
 *
 * **Property 12: Zero Vector Handling**
 * **Validates: Requirements 5.4**
 *
 * For any embedding vector A, cosineSimilarity(zero_vector, A) SHALL equal 0,
 * where zero_vector is a vector of all zeros.
 */
class CosineSimilarityZeroVectorPropertyTest extends TestCase
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
     * Helper: generate a zero vector of given length.
     */
    private function zeroVector(int $length): array
    {
        return array_fill(0, $length, 0.0);
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
     * Property: cosineSimilarity(zero_vector, A) == 0 for random 128-dimensional vectors.
     *
     * **Validates: Requirements 5.4**
     */
    public function test_property_zero_vector_first_argument_128_dimensions(): void
    {
        for ($scenario = 1; $scenario <= 50; $scenario++) {
            $zero = $this->zeroVector(128);
            $a = $this->randomVector(128);

            $similarity = $this->cosineSimilarity($zero, $a);

            $this->assertEqualsWithDelta(
                0.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: cosineSimilarity(zero, A) should be 0.0, got {$similarity}."
            );
        }
    }

    /**
     * Property: cosineSimilarity(A, zero_vector) == 0 for random 128-dimensional vectors.
     *
     * **Validates: Requirements 5.4**
     */
    public function test_property_zero_vector_second_argument_128_dimensions(): void
    {
        for ($scenario = 1; $scenario <= 50; $scenario++) {
            $a = $this->randomVector(128);
            $zero = $this->zeroVector(128);

            $similarity = $this->cosineSimilarity($a, $zero);

            $this->assertEqualsWithDelta(
                0.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: cosineSimilarity(A, zero) should be 0.0, got {$similarity}."
            );
        }
    }

    /**
     * Property: cosineSimilarity(zero, zero) == 0.
     *
     * **Validates: Requirements 5.4**
     */
    public function test_property_both_zero_vectors(): void
    {
        $lengths = [1, 2, 5, 10, 32, 64, 128];

        foreach ($lengths as $length) {
            $zero = $this->zeroVector($length);

            $similarity = $this->cosineSimilarity($zero, $zero);

            $this->assertEqualsWithDelta(
                0.0,
                $similarity,
                1e-10,
                "Length {$length}: cosineSimilarity(zero, zero) should be 0.0, got {$similarity}."
            );
        }
    }

    /**
     * Property: zero vector handling holds for various vector lengths.
     *
     * **Validates: Requirements 5.4**
     */
    public function test_property_zero_vector_various_lengths(): void
    {
        $lengths = [1, 2, 5, 10, 32, 64, 128, 256];

        foreach ($lengths as $length) {
            for ($scenario = 1; $scenario <= 5; $scenario++) {
                $zero = $this->zeroVector($length);
                $a = $this->randomVector($length);

                // zero as first argument
                $sim1 = $this->cosineSimilarity($zero, $a);
                $this->assertEqualsWithDelta(
                    0.0,
                    $sim1,
                    1e-10,
                    "Length {$length}, Scenario {$scenario}: cosineSimilarity(zero, A) should be 0.0, got {$sim1}."
                );

                // zero as second argument
                $sim2 = $this->cosineSimilarity($a, $zero);
                $this->assertEqualsWithDelta(
                    0.0,
                    $sim2,
                    1e-10,
                    "Length {$length}, Scenario {$scenario}: cosineSimilarity(A, zero) should be 0.0, got {$sim2}."
                );
            }
        }
    }

    /**
     * Property: zero vector handling holds for all-positive non-zero vectors.
     *
     * **Validates: Requirements 5.4**
     */
    public function test_property_zero_vector_with_all_positive_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(1, 128);
            $zero = $this->zeroVector($length);
            $a = $this->randomVector($length, 0.01, 10.0); // all positive

            $similarity = $this->cosineSimilarity($zero, $a);

            $this->assertEqualsWithDelta(
                0.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: cosineSimilarity(zero, positive_vector) should be 0.0, got {$similarity}."
            );
        }
    }
}
