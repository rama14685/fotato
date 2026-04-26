<?php

namespace Tests\Unit;

use App\Http\Controllers\FaceScanController;
use InvalidArgumentException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Property-Based Test for Task 13.5: Dimension Mismatch Error
 *
 * **Property 13: Dimension Mismatch Error**
 * **Validates: Requirements 5.5**
 *
 * For any two embedding vectors with different dimensions,
 * the cosineSimilarity function SHALL throw an InvalidArgumentException.
 */
class CosineSimilarityDimensionMismatchPropertyTest extends TestCase
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
    private function randomVector(int $length): array
    {
        $vec = [];
        for ($i = 0; $i < $length; $i++) {
            $vec[] = (lcg_value() * 2.0) - 1.0;
        }
        return $vec;
    }

    /**
     * Property: InvalidArgumentException is thrown when vectors have different dimensions.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_dimension_mismatch_throws_exception(): void
    {
        for ($scenario = 1; $scenario <= 50; $scenario++) {
            // Generate two different lengths
            $length1 = rand(1, 200);
            do {
                $length2 = rand(1, 200);
            } while ($length2 === $length1);

            $a = $this->randomVector($length1);
            $b = $this->randomVector($length2);

            $this->expectException(InvalidArgumentException::class);

            $this->cosineSimilarity($a, $b);

            // Reset expectation for next iteration by catching manually
            // (PHPUnit stops after first expectException match, so we use try/catch below)
        }
    }

    /**
     * Property: exception is thrown for all mismatched dimension pairs (manual catch).
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_dimension_mismatch_all_scenarios(): void
    {
        for ($scenario = 1; $scenario <= 50; $scenario++) {
            $length1 = rand(1, 200);
            do {
                $length2 = rand(1, 200);
            } while ($length2 === $length1);

            $a = $this->randomVector($length1);
            $b = $this->randomVector($length2);

            $exceptionThrown = false;
            try {
                $this->cosineSimilarity($a, $b);
            } catch (InvalidArgumentException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Scenario {$scenario}: Expected InvalidArgumentException for vectors of length " .
                "{$length1} and {$length2}, but no exception was thrown."
            );
        }
    }

    /**
     * Property: exception is thrown when first vector is longer.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_dimension_mismatch_first_longer(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length1 = rand(10, 200);
            $length2 = rand(1, $length1 - 1); // strictly shorter

            $a = $this->randomVector($length1);
            $b = $this->randomVector($length2);

            $exceptionThrown = false;
            try {
                $this->cosineSimilarity($a, $b);
            } catch (InvalidArgumentException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Scenario {$scenario}: Expected exception when first vector (len={$length1}) " .
                "is longer than second (len={$length2})."
            );
        }
    }

    /**
     * Property: exception is thrown when second vector is longer.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_dimension_mismatch_second_longer(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length2 = rand(10, 200);
            $length1 = rand(1, $length2 - 1); // strictly shorter

            $a = $this->randomVector($length1);
            $b = $this->randomVector($length2);

            $exceptionThrown = false;
            try {
                $this->cosineSimilarity($a, $b);
            } catch (InvalidArgumentException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Scenario {$scenario}: Expected exception when second vector (len={$length2}) " .
                "is longer than first (len={$length1})."
            );
        }
    }

    /**
     * Property: exception is thrown for common mismatched sizes (e.g., 128 vs 64).
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_dimension_mismatch_common_sizes(): void
    {
        $mismatchedPairs = [
            [128, 64],
            [128, 256],
            [128, 1],
            [1, 128],
            [64, 32],
            [256, 128],
            [10, 11],
            [100, 99],
        ];

        foreach ($mismatchedPairs as [$len1, $len2]) {
            $a = $this->randomVector($len1);
            $b = $this->randomVector($len2);

            $exceptionThrown = false;
            try {
                $this->cosineSimilarity($a, $b);
            } catch (InvalidArgumentException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Expected InvalidArgumentException for vectors of length {$len1} and {$len2}."
            );
        }
    }

    /**
     * Property: no exception is thrown when dimensions match (sanity check).
     *
     * **Validates: Requirements 5.5**
     */
    public function test_property_no_exception_when_dimensions_match(): void
    {
        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $length = rand(1, 200);
            $a = $this->randomVector($length);
            $b = $this->randomVector($length);

            $exceptionThrown = false;
            try {
                $this->cosineSimilarity($a, $b);
            } catch (InvalidArgumentException $e) {
                $exceptionThrown = true;
            }

            $this->assertFalse(
                $exceptionThrown,
                "Scenario {$scenario}: No exception should be thrown for matching dimensions (len={$length})."
            );
        }
    }
}
