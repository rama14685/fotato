<?php

namespace Tests\Unit;

use App\Http\Controllers\FaceScanController;
use InvalidArgumentException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Unit Tests for Task 13.6: cosineSimilarity Edge Cases
 *
 * Tests specific edge cases for the cosineSimilarity private method:
 * - Identical vectors (should return 1.0)
 * - Orthogonal vectors (should return 0)
 * - Opposite vectors (should return -1.0)
 * - Zero magnitude vectors (should return 0)
 *
 * Requirements: 5.3, 5.4, 5.6
 */
class CosineSimilarityEdgeCasesTest extends TestCase
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

    // -------------------------------------------------------------------------
    // Identical vectors → similarity = 1.0
    // -------------------------------------------------------------------------

    /**
     * Test: identical 128-dimensional vectors return 1.0.
     *
     * Validates: Requirements 5.3
     */
    public function test_identical_128d_vectors_return_one(): void
    {
        $vec = array_fill(0, 128, 0.5);

        $result = $this->cosineSimilarity($vec, $vec);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10, 'Identical vectors should have similarity 1.0.');
    }

    /**
     * Test: identical single-element vectors return 1.0.
     *
     * Validates: Requirements 5.3
     */
    public function test_identical_single_element_vectors_return_one(): void
    {
        $vec = [3.14];

        $result = $this->cosineSimilarity($vec, $vec);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10, 'Identical single-element vectors should have similarity 1.0.');
    }

    /**
     * Test: identical vectors with mixed positive and negative values return 1.0.
     *
     * Validates: Requirements 5.3
     */
    public function test_identical_mixed_sign_vectors_return_one(): void
    {
        $vec = [1.0, -2.0, 3.0, -4.0, 5.0, -6.0, 7.0, -8.0];

        $result = $this->cosineSimilarity($vec, $vec);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10, 'Identical mixed-sign vectors should have similarity 1.0.');
    }

    /**
     * Test: a vector compared to a scaled copy of itself returns 1.0.
     *
     * Validates: Requirements 5.3
     */
    public function test_vector_and_scaled_copy_return_one(): void
    {
        $vec = [1.0, 2.0, 3.0, 4.0];
        $scaled = array_map(fn($x) => $x * 5.0, $vec);

        $result = $this->cosineSimilarity($vec, $scaled);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10, 'A vector and its positive scalar multiple should have similarity 1.0.');
    }

    // -------------------------------------------------------------------------
    // Orthogonal vectors → similarity = 0
    // -------------------------------------------------------------------------

    /**
     * Test: standard basis vectors e1 and e2 are orthogonal (similarity = 0).
     *
     * Validates: Requirements 5.6
     */
    public function test_orthogonal_standard_basis_vectors_return_zero(): void
    {
        $e1 = [1.0, 0.0, 0.0];
        $e2 = [0.0, 1.0, 0.0];

        $result = $this->cosineSimilarity($e1, $e2);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, 'Orthogonal standard basis vectors should have similarity 0.');
    }

    /**
     * Test: 128-dimensional orthogonal vectors return 0.
     *
     * Vectors: [1, 0, 0, ..., 0] and [0, 1, 0, ..., 0]
     *
     * Validates: Requirements 5.6
     */
    public function test_orthogonal_128d_vectors_return_zero(): void
    {
        $e1 = array_fill(0, 128, 0.0);
        $e1[0] = 1.0;

        $e2 = array_fill(0, 128, 0.0);
        $e2[1] = 1.0;

        $result = $this->cosineSimilarity($e1, $e2);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, '128-dimensional orthogonal vectors should have similarity 0.');
    }

    /**
     * Test: [1, 1] and [-1, 1] are orthogonal (dot product = 0).
     *
     * Validates: Requirements 5.6
     */
    public function test_orthogonal_2d_vectors_return_zero(): void
    {
        $a = [1.0, 1.0];
        $b = [-1.0, 1.0];

        $result = $this->cosineSimilarity($a, $b);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, '[1,1] and [-1,1] should have similarity 0 (orthogonal).');
    }

    // -------------------------------------------------------------------------
    // Opposite vectors → similarity = -1.0
    // -------------------------------------------------------------------------

    /**
     * Test: a vector and its negation return -1.0.
     *
     * Validates: Requirements 5.6
     */
    public function test_opposite_vectors_return_minus_one(): void
    {
        $vec = [1.0, 2.0, 3.0, 4.0];
        $neg = array_map(fn($x) => -$x, $vec);

        $result = $this->cosineSimilarity($vec, $neg);

        $this->assertEqualsWithDelta(-1.0, $result, 1e-10, 'A vector and its negation should have similarity -1.0.');
    }

    /**
     * Test: 128-dimensional vector and its negation return -1.0.
     *
     * Validates: Requirements 5.6
     */
    public function test_opposite_128d_vectors_return_minus_one(): void
    {
        $vec = array_fill(0, 128, 1.0);
        $neg = array_fill(0, 128, -1.0);

        $result = $this->cosineSimilarity($vec, $neg);

        $this->assertEqualsWithDelta(-1.0, $result, 1e-10, '128-dimensional opposite vectors should have similarity -1.0.');
    }

    /**
     * Test: [1, 1, 1, ...] (128 dims) and [-1, -1, -1, ...] return -1.0.
     *
     * dot product = -128, magnitudes both = sqrt(128), similarity = -128/128 = -1.0
     *
     * Validates: Requirements 5.6
     */
    public function test_all_ones_vs_all_minus_ones_128d(): void
    {
        $ones = array_fill(0, 128, 1.0);
        $neg_ones = array_fill(0, 128, -1.0);

        $result = $this->cosineSimilarity($ones, $neg_ones);

        $this->assertEqualsWithDelta(-1.0, $result, 1e-10, '[1,...,1] and [-1,...,-1] should have similarity -1.0.');
    }

    // -------------------------------------------------------------------------
    // Zero magnitude vectors → similarity = 0
    // -------------------------------------------------------------------------

    /**
     * Test: zero vector as first argument returns 0.
     *
     * Validates: Requirements 5.4
     */
    public function test_zero_vector_first_argument_returns_zero(): void
    {
        $zero = array_fill(0, 128, 0.0);
        $vec  = array_fill(0, 128, 1.0);

        $result = $this->cosineSimilarity($zero, $vec);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, 'Zero vector as first argument should return 0.');
    }

    /**
     * Test: zero vector as second argument returns 0.
     *
     * Validates: Requirements 5.4
     */
    public function test_zero_vector_second_argument_returns_zero(): void
    {
        $vec  = array_fill(0, 128, 1.0);
        $zero = array_fill(0, 128, 0.0);

        $result = $this->cosineSimilarity($vec, $zero);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, 'Zero vector as second argument should return 0.');
    }

    /**
     * Test: both zero vectors return 0.
     *
     * Validates: Requirements 5.4
     */
    public function test_both_zero_vectors_return_zero(): void
    {
        $zero = array_fill(0, 128, 0.0);

        $result = $this->cosineSimilarity($zero, $zero);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, 'Both zero vectors should return 0.');
    }

    /**
     * Test: single-element zero vector returns 0.
     *
     * Validates: Requirements 5.4
     */
    public function test_single_element_zero_vector_returns_zero(): void
    {
        $zero = [0.0];
        $vec  = [5.0];

        $result = $this->cosineSimilarity($zero, $vec);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10, 'Single-element zero vector should return 0.');
    }

    // -------------------------------------------------------------------------
    // Dimension mismatch → InvalidArgumentException
    // -------------------------------------------------------------------------

    /**
     * Test: vectors with different dimensions throw InvalidArgumentException.
     *
     * Validates: Requirements 5.5
     */
    public function test_dimension_mismatch_throws_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $a = [1.0, 2.0, 3.0];
        $b = [1.0, 2.0];

        $this->cosineSimilarity($a, $b);
    }

    /**
     * Test: 128-dimensional vs 64-dimensional throws InvalidArgumentException.
     *
     * Validates: Requirements 5.5
     */
    public function test_128d_vs_64d_throws_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $a = array_fill(0, 128, 1.0);
        $b = array_fill(0, 64, 1.0);

        $this->cosineSimilarity($a, $b);
    }

    // -------------------------------------------------------------------------
    // Known numerical values
    // -------------------------------------------------------------------------

    /**
     * Test: [1, 0] and [0, 1] have similarity 0 (orthogonal).
     *
     * Validates: Requirements 5.6
     */
    public function test_known_orthogonal_2d(): void
    {
        $result = $this->cosineSimilarity([1.0, 0.0], [0.0, 1.0]);

        $this->assertEqualsWithDelta(0.0, $result, 1e-10);
    }

    /**
     * Test: [1, 1] and [1, 1] have similarity 1.0 (identical direction).
     *
     * Validates: Requirements 5.3
     */
    public function test_known_identical_direction_2d(): void
    {
        $result = $this->cosineSimilarity([1.0, 1.0], [1.0, 1.0]);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10);
    }

    /**
     * Test: [1, 0] and [1, 0] have similarity 1.0.
     *
     * Validates: Requirements 5.3
     */
    public function test_known_same_unit_vector(): void
    {
        $result = $this->cosineSimilarity([1.0, 0.0], [1.0, 0.0]);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10);
    }

    /**
     * Test: [3, 4] and [3, 4] have similarity 1.0.
     *
     * Validates: Requirements 5.3
     */
    public function test_known_3_4_vector_identity(): void
    {
        $result = $this->cosineSimilarity([3.0, 4.0], [3.0, 4.0]);

        $this->assertEqualsWithDelta(1.0, $result, 1e-10);
    }

    /**
     * Test: [1, 0] and [-1, 0] have similarity -1.0 (opposite directions).
     *
     * Validates: Requirements 5.6
     */
    public function test_known_opposite_unit_vectors(): void
    {
        $result = $this->cosineSimilarity([1.0, 0.0], [-1.0, 0.0]);

        $this->assertEqualsWithDelta(-1.0, $result, 1e-10);
    }
}
