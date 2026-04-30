<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;

/**
 * Unit Tests for Task 3.4: Edge Case Handling
 *
 * Tests zero magnitude vectors, dimension mismatch scenarios, and non-numeric value handling.
 * Validates Requirements: 2.1, 2.2, 3.2, 3.3, 9.3
 *
 * This test suite covers at least 20 different edge cases to ensure robust
 * error handling and graceful degradation in the face matching service.
 */
class EdgeCaseHandlingTest extends TestCase
{
    private CosineSimilarityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CosineSimilarityCalculator();
    }

    /**
     * Helper: Generate a valid 128-dimensional embedding
     */
    private function generateValidEmbedding(): array
    {
        return array_fill(0, 128, 0.5);
    }

    /**
     * Helper: Generate a zero magnitude embedding (all zeros)
     */
    private function generateZeroMagnitudeEmbedding(): array
    {
        return array_fill(0, 128, 0.0);
    }

    // ============================================================================
    // ZERO MAGNITUDE VECTOR TESTS (Requirements 2.1, 2.2, 2.3, 2.4, 2.5)
    // ============================================================================

    /**
     * Test 1: Zero magnitude customer embedding returns 0.0 similarity
     * Requirement: 2.1
     */
    public function test_zero_magnitude_customer_embedding_returns_zero_similarity(): void
    {
        $zeroEmbedding = $this->generateZeroMagnitudeEmbedding();
        $validEmbedding = $this->generateValidEmbedding();

        $similarity = $this->calculator->calculateSimilarity($zeroEmbedding, $validEmbedding);

        $this->assertEquals(0.0, $similarity);
    }

    /**
     * Test 2: Zero magnitude photo embedding returns 0.0 similarity
     * Requirement: 2.1
     */
    public function test_zero_magnitude_photo_embedding_returns_zero_similarity(): void
    {
        $validEmbedding = $this->generateValidEmbedding();
        $zeroEmbedding = $this->generateZeroMagnitudeEmbedding();

        $similarity = $this->calculator->calculateSimilarity($validEmbedding, $zeroEmbedding);

        $this->assertEquals(0.0, $similarity);
    }

    /**
     * Test 3: Both embeddings with zero magnitude returns 0.0 similarity
     * Requirement: 2.2
     */
    public function test_both_zero_magnitude_embeddings_return_zero_similarity(): void
    {
        $zeroEmbedding1 = $this->generateZeroMagnitudeEmbedding();
        $zeroEmbedding2 = $this->generateZeroMagnitudeEmbedding();

        $similarity = $this->calculator->calculateSimilarity($zeroEmbedding1, $zeroEmbedding2);

        $this->assertEquals(0.0, $similarity);
    }

    /**
     * Test 4: Zero magnitude does not throw division-by-zero exception
     * Requirement: 2.3
     */
    public function test_zero_magnitude_does_not_throw_exception(): void
    {
        $zeroEmbedding = $this->generateZeroMagnitudeEmbedding();
        $validEmbedding = $this->generateValidEmbedding();

        // Should not throw exception
        try {
            $this->calculator->calculateSimilarity($zeroEmbedding, $validEmbedding);
            $this->assertTrue(true);
        } catch (\DivisionByZeroError $e) {
            $this->fail("Division by zero exception should not be thrown");
        }
    }

    /**
     * Test 5: Zero magnitude does not return NaN
     * Requirement: 2.4
     */
    public function test_zero_magnitude_does_not_return_nan(): void
    {
        $zeroEmbedding = $this->generateZeroMagnitudeEmbedding();
        $validEmbedding = $this->generateValidEmbedding();

        $similarity = $this->calculator->calculateSimilarity($zeroEmbedding, $validEmbedding);

        $this->assertFalse(is_nan($similarity), "Similarity should not be NaN");
    }

    /**
     * Test 6: Zero magnitude does not return Infinity
     * Requirement: 2.4
     */
    public function test_zero_magnitude_does_not_return_infinity(): void
    {
        $zeroEmbedding = $this->generateZeroMagnitudeEmbedding();
        $validEmbedding = $this->generateValidEmbedding();

        $similarity = $this->calculator->calculateSimilarity($zeroEmbedding, $validEmbedding);

        $this->assertFalse(is_infinite($similarity), "Similarity should not be Infinity");
    }

    /**
     * Test 7: Zero magnitude vector is handled without exception
     * Requirement: 2.5
     */
    public function test_zero_magnitude_handled_without_exception(): void
    {
        $zeroEmbedding = $this->generateZeroMagnitudeEmbedding();
        $validEmbedding = $this->generateValidEmbedding();

        // Should not throw exception and should return 0.0
        $similarity = $this->calculator->calculateSimilarity($zeroEmbedding, $validEmbedding);
        $this->assertEquals(0.0, $similarity);
    }

    /**
     * Test 8: Both zero magnitude vectors handled without exception
     * Requirement: 2.5
     */
    public function test_both_zero_magnitude_vectors_handled_without_exception(): void
    {
        $zeroEmbedding1 = $this->generateZeroMagnitudeEmbedding();
        $zeroEmbedding2 = $this->generateZeroMagnitudeEmbedding();

        // Should not throw exception and should return 0.0
        $similarity = $this->calculator->calculateSimilarity($zeroEmbedding1, $zeroEmbedding2);
        $this->assertEquals(0.0, $similarity);
    }

    // ============================================================================
    // DIMENSION MISMATCH TESTS (Requirements 3.2, 3.3)
    // ============================================================================

    /**
     * Test 9: Customer embedding with too few dimensions throws exception
     * Requirement: 3.2
     */
    public function test_customer_embedding_too_few_dimensions_throws_exception(): void
    {
        $shortEmbedding = array_fill(0, 64, 0.5);
        $validEmbedding = $this->generateValidEmbedding();

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->calculateSimilarity($shortEmbedding, $validEmbedding);
    }

    /**
     * Test 10: Photo embedding with too few dimensions throws exception
     * Requirement: 3.2
     */
    public function test_photo_embedding_too_few_dimensions_throws_exception(): void
    {
        $validEmbedding = $this->generateValidEmbedding();
        $shortEmbedding = array_fill(0, 64, 0.5);

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->calculateSimilarity($validEmbedding, $shortEmbedding);
    }

    /**
     * Test 11: Customer embedding with too many dimensions throws exception
     * Requirement: 3.2
     */
    public function test_customer_embedding_too_many_dimensions_throws_exception(): void
    {
        $longEmbedding = array_fill(0, 256, 0.5);
        $validEmbedding = $this->generateValidEmbedding();

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->calculateSimilarity($longEmbedding, $validEmbedding);
    }

    /**
     * Test 12: Photo embedding with too many dimensions throws exception
     * Requirement: 3.2
     */
    public function test_photo_embedding_too_many_dimensions_throws_exception(): void
    {
        $validEmbedding = $this->generateValidEmbedding();
        $longEmbedding = array_fill(0, 256, 0.5);

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->calculateSimilarity($validEmbedding, $longEmbedding);
    }

    /**
     * Test 13: Empty embedding throws exception
     * Requirement: 3.2
     */
    public function test_empty_embedding_throws_exception(): void
    {
        $emptyEmbedding = [];
        $validEmbedding = $this->generateValidEmbedding();

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->calculateSimilarity($emptyEmbedding, $validEmbedding);
    }

    /**
     * Test 14: Dimension mismatch error message includes actual dimensions
     * Requirement: 3.3
     */
    public function test_dimension_mismatch_error_includes_actual_dimensions(): void
    {
        $shortEmbedding = array_fill(0, 64, 0.5);
        $validEmbedding = $this->generateValidEmbedding();

        try {
            $this->calculator->calculateSimilarity($shortEmbedding, $validEmbedding);
            $this->fail("Expected InvalidEmbeddingException");
        } catch (InvalidEmbeddingException $e) {
            $this->assertStringContainsString('64', $e->getMessage());
            $this->assertStringContainsString('128', $e->getMessage());
        }
    }

    /**
     * Test 15: Dimension mismatch error message includes context
     * Requirement: 3.3
     */
    public function test_dimension_mismatch_error_includes_context(): void
    {
        $shortEmbedding = array_fill(0, 64, 0.5);
        $validEmbedding = $this->generateValidEmbedding();

        try {
            $this->calculator->calculateSimilarity($shortEmbedding, $validEmbedding);
            $this->fail("Expected InvalidEmbeddingException");
        } catch (InvalidEmbeddingException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('context', $context);
            $this->assertEquals('customer', $context['context']);
        }
    }

    // ============================================================================
    // NON-NUMERIC VALUE TESTS (Requirements 9.3)
    // ============================================================================

    /**
     * Test 16: String value in embedding throws exception
     * Requirement: 9.3
     */
    public function test_string_value_in_embedding_throws_exception(): void
    {
        $embedding = $this->generateValidEmbedding();
        $embedding[50] = 'invalid';

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }

    /**
     * Test 17: Null value in embedding throws exception
     * Requirement: 9.3
     */
    public function test_null_value_in_embedding_throws_exception(): void
    {
        $embedding = $this->generateValidEmbedding();
        $embedding[50] = null;

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }

    /**
     * Test 18: Boolean value in embedding throws exception
     * Requirement: 9.3
     */
    public function test_boolean_value_in_embedding_throws_exception(): void
    {
        $embedding = $this->generateValidEmbedding();
        $embedding[50] = true;

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }

    /**
     * Test 19: Array value in embedding throws exception
     * Requirement: 9.3
     */
    public function test_array_value_in_embedding_throws_exception(): void
    {
        $embedding = $this->generateValidEmbedding();
        $embedding[50] = [1, 2, 3];

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }

    /**
     * Test 20: Object value in embedding throws exception
     * Requirement: 9.3
     */
    public function test_object_value_in_embedding_throws_exception(): void
    {
        $embedding = $this->generateValidEmbedding();
        $embedding[50] = new \stdClass();

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }

    /**
     * Test 21: Non-numeric error message is clear
     * Requirement: 9.3
     */
    public function test_non_numeric_error_message_is_clear(): void
    {
        $embedding = $this->generateValidEmbedding();
        $embedding[50] = 'invalid';

        try {
            $this->calculator->validateEmbedding($embedding, 'test');
            $this->fail("Expected InvalidEmbeddingException");
        } catch (InvalidEmbeddingException $e) {
            $this->assertStringContainsString('numeric', strtolower($e->getMessage()));
        }
    }

    /**
     * Test 22: Numeric strings are accepted
     * Requirement: 9.3
     */
    public function test_numeric_strings_are_accepted(): void
    {
        $embedding = array_fill(0, 128, '0.5');

        // Should not throw exception
        $this->calculator->validateEmbedding($embedding, 'test');
        $this->assertTrue(true);
    }

    /**
     * Test 23: Very small values (near zero but not zero) are handled correctly
     * Requirement: 2.1
     */
    public function test_very_small_values_handled_correctly(): void
    {
        $embedding1 = array_fill(0, 128, 1e-10);
        $embedding2 = array_fill(0, 128, 1e-10);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Should be close to 1.0 (identical vectors)
        $this->assertGreaterThan(0.99, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
    }

    /**
     * Test 24: Very large values are handled correctly
     * Requirement: 2.1
     */
    public function test_very_large_values_handled_correctly(): void
    {
        $embedding1 = array_fill(0, 128, 1e10);
        $embedding2 = array_fill(0, 128, 1e10);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Should be close to 1.0 (identical vectors)
        $this->assertGreaterThan(0.99, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
    }

    /**
     * Test 25: Mixed positive and negative values are handled correctly
     * Requirement: 2.1
     */
    public function test_mixed_positive_negative_values_handled_correctly(): void
    {
        $embedding1 = [];
        $embedding2 = [];
        for ($i = 0; $i < 128; $i++) {
            $embedding1[] = ($i % 2 === 0) ? 0.5 : -0.5;
            $embedding2[] = ($i % 2 === 0) ? 0.5 : -0.5;
        }

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Should be close to 1.0 (identical vectors)
        $this->assertGreaterThan(0.99, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
    }
}
