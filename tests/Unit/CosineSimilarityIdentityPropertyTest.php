<?php

namespace Tests\Unit;

use App\Http\Controllers\FaceScanController;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Property-Based Test for Task 13.3: Cosine Similarity Identity
 *
 * **Property 11: Cosine Similarity Identity**
 * **Validates: Requirements 5.3**
 *
 * For any embedding vector A, cosineSimilarity(A, A) SHALL equal 1.0.
 */
class CosineSimilarityIdentityPropertyTest extends TestCase
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
     * Helper: generate a random non-zero float vector of given length.
     */
    private function randomNonZeroVector(int $length): array
    {
        do {
            $vec = [];
            for ($i = 0; $i < $length; $i++) {
                $vec[] = (lcg_value() * 2.0) - 1.0; // [-1, 1)
            }
            $magnitude = sqrt(array_sum(array_map(fn($x) => $x ** 2, $vec)));
        } while ($magnitude < 1e-10); // ensure non-zero magnitude

        return $vec;
    }

    /**
     * Property: cosineSimilarity(A, A) == 1.0 for random 128-dimensional vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_identity_with_128_dimensional_vectors(): void
    {
        for ($scenario = 1; $scenario <= 50; $scenario++) {
            $a = $this->randomNonZeroVector(128);

            $similarity = $this->cosineSimilarity($a, $a);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: cosineSimilarity(A, A) should be 1.0, got {$similarity}."
            );
        }
    }

    /**
     * Property: identity holds for vectors of various lengths.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_identity_with_various_vector_lengths(): void
    {
        $lengths = [1, 2, 5, 10, 32, 64, 128, 256];

        foreach ($lengths as $length) {
            for ($scenario = 1; $scenario <= 10; $scenario++) {
                $a = $this->randomNonZeroVector($length);

                $similarity = $this->cosineSimilarity($a, $a);

                $this->assertEqualsWithDelta(
                    1.0,
                    $similarity,
                    1e-10,
                    "Length {$length}, Scenario {$scenario}: cosineSimilarity(A, A) should be 1.0, got {$similarity}."
                );
            }
        }
    }

    /**
     * Property: identity holds for all-positive vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_identity_with_all_positive_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(1, 128);
            $a = [];
            for ($i = 0; $i < $length; $i++) {
                $a[] = lcg_value() * 10.0 + 0.01; // (0.01, 10.01]
            }

            $similarity = $this->cosineSimilarity($a, $a);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: identity failed for all-positive vector, got {$similarity}."
            );
        }
    }

    /**
     * Property: identity holds for all-negative vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_identity_with_all_negative_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(1, 128);
            $a = [];
            for ($i = 0; $i < $length; $i++) {
                $a[] = -(lcg_value() * 10.0 + 0.01); // (-10.01, -0.01]
            }

            $similarity = $this->cosineSimilarity($a, $a);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: identity failed for all-negative vector, got {$similarity}."
            );
        }
    }

    /**
     * Property: identity holds for unit vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_identity_with_unit_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(2, 128);
            $raw = $this->randomNonZeroVector($length);
            $mag = sqrt(array_sum(array_map(fn($x) => $x ** 2, $raw)));
            $a = array_map(fn($x) => $x / $mag, $raw);

            $similarity = $this->cosineSimilarity($a, $a);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: identity failed for unit vector, got {$similarity}."
            );
        }
    }

    /**
     * Property: identity holds for large-magnitude vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_identity_with_large_magnitude_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(10, 128);
            $a = [];
            for ($i = 0; $i < $length; $i++) {
                $a[] = (lcg_value() * 2000.0) - 1000.0; // [-1000, 1000)
            }

            // Ensure non-zero magnitude
            $mag = sqrt(array_sum(array_map(fn($x) => $x ** 2, $a)));
            if ($mag < 1e-10) {
                $a[0] = 1.0; // force non-zero
            }

            $similarity = $this->cosineSimilarity($a, $a);

            $this->assertEqualsWithDelta(
                1.0,
                $similarity,
                1e-10,
                "Scenario {$scenario}: identity failed for large-magnitude vector, got {$similarity}."
            );
        }
    }
}
