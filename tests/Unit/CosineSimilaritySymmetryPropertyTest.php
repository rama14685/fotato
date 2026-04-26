<?php

namespace Tests\Unit;

use App\Http\Controllers\FaceScanController;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Property-Based Test for Task 13.1: Cosine Similarity Symmetry
 *
 * **Property 9: Cosine Similarity Symmetry**
 * **Validates: Requirements 5.3**
 *
 * For any two embedding vectors A and B,
 * cosineSimilarity(A, B) SHALL equal cosineSimilarity(B, A).
 */
class CosineSimilaritySymmetryPropertyTest extends TestCase
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
     * Property: cosineSimilarity(A, B) == cosineSimilarity(B, A)
     * for random 128-dimensional vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_symmetry_with_128_dimensional_vectors(): void
    {
        for ($scenario = 1; $scenario <= 50; $scenario++) {
            $a = $this->randomVector(128);
            $b = $this->randomVector(128);

            $ab = $this->cosineSimilarity($a, $b);
            $ba = $this->cosineSimilarity($b, $a);

            $this->assertEqualsWithDelta(
                $ab,
                $ba,
                1e-10,
                "Scenario {$scenario}: cosineSimilarity(A, B) should equal cosineSimilarity(B, A). " .
                "Got {$ab} vs {$ba}."
            );
        }
    }

    /**
     * Property: symmetry holds for vectors of various lengths.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_symmetry_with_various_vector_lengths(): void
    {
        $lengths = [1, 2, 5, 10, 32, 64, 128, 256];

        foreach ($lengths as $length) {
            for ($scenario = 1; $scenario <= 10; $scenario++) {
                $a = $this->randomVector($length);
                $b = $this->randomVector($length);

                $ab = $this->cosineSimilarity($a, $b);
                $ba = $this->cosineSimilarity($b, $a);

                $this->assertEqualsWithDelta(
                    $ab,
                    $ba,
                    1e-10,
                    "Length {$length}, Scenario {$scenario}: symmetry violated. Got {$ab} vs {$ba}."
                );
            }
        }
    }

    /**
     * Property: symmetry holds when one vector has all positive values
     * and the other has mixed signs.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_symmetry_with_mixed_sign_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(10, 128);
            $a = $this->randomVector($length, 0.0, 1.0);   // all positive
            $b = $this->randomVector($length, -1.0, 1.0);  // mixed signs

            $ab = $this->cosineSimilarity($a, $b);
            $ba = $this->cosineSimilarity($b, $a);

            $this->assertEqualsWithDelta(
                $ab,
                $ba,
                1e-10,
                "Scenario {$scenario}: symmetry violated with mixed-sign vectors. Got {$ab} vs {$ba}."
            );
        }
    }

    /**
     * Property: symmetry holds for unit vectors.
     *
     * **Validates: Requirements 5.3**
     */
    public function test_property_symmetry_with_unit_vectors(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(2, 128);

            // Build a unit vector by normalising a random vector
            $raw = $this->randomVector($length);
            $mag = sqrt(array_sum(array_map(fn($x) => $x ** 2, $raw)));
            $a = array_map(fn($x) => $x / $mag, $raw);

            $raw2 = $this->randomVector($length);
            $mag2 = sqrt(array_sum(array_map(fn($x) => $x ** 2, $raw2)));
            $b = array_map(fn($x) => $x / $mag2, $raw2);

            $ab = $this->cosineSimilarity($a, $b);
            $ba = $this->cosineSimilarity($b, $a);

            $this->assertEqualsWithDelta(
                $ab,
                $ba,
                1e-10,
                "Scenario {$scenario}: symmetry violated for unit vectors. Got {$ab} vs {$ba}."
            );
        }
    }
}
