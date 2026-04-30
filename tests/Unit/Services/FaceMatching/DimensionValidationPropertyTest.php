<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;

/**
 * Property-Based Test for Task 3.3: Dimension Validation
 *
 * **Property 6: Dimension Validation**
 * **Validates: Requirements 3.1, 3.4**
 *
 * Verifies that dimension validation works correctly for all invalid dimensions.
 * The calculator must reject embeddings that don't have exactly 128 dimensions
 * and provide clear error messages with the actual dimension count.
 *
 * For any embedding with dimensions != 128:
 * - An InvalidEmbeddingException must be thrown
 * - The exception message must include the actual dimension count
 * - The exception must include context information
 */
class DimensionValidationPropertyTest extends TestCase
{
    private CosineSimilarityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CosineSimilarityCalculator();
    }

    /**
     * Helper: Generate an embedding with specified dimensions
     */
    private function generateEmbeddingWithDimensions(int $dimensions): array
    {
        $embedding = [];
        for ($i = 0; $i < $dimensions; $i++) {
            $embedding[] = (float)($i % 10) / 10.0;
        }
        return $embedding;
    }

    /**
     * Property: Dimension validation rejects all invalid dimensions
     * 
     * For any dimension count that is not 128, validation must throw
     * InvalidEmbeddingException with appropriate error message.
     *
     * Tests dimensions: 0, 1, 10, 50, 64, 100, 127, 129, 150, 200, 256, 512, 1000
     *
     * **Validates: Requirements 3.1, 3.4**
     */
    public function test_property_dimension_validation_rejects_invalid_dimensions(): void
    {
        $invalidDimensions = [0, 1, 10, 50, 64, 100, 127, 129, 150, 200, 256, 512, 1000];

        foreach ($invalidDimensions as $dimension) {
            $embedding = $this->generateEmbeddingWithDimensions($dimension);

            $this->expectException(InvalidEmbeddingException::class);
            $this->calculator->validateEmbedding($embedding, 'test');
        }
    }

    /**
     * Property: Dimension validation accepts exactly 128 dimensions
     * 
     * For embeddings with exactly 128 dimensions and all numeric values,
     * validation must succeed without throwing an exception.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_property_dimension_validation_accepts_128_dimensions(): void
    {
        $embedding = $this->generateEmbeddingWithDimensions(128);

        // Should not throw exception
        $this->calculator->validateEmbedding($embedding, 'test');
        $this->assertTrue(true);
    }

    /**
     * Property: Error messages include actual dimension count
     * 
     * When validation fails, the exception message must include
     * the actual dimension count for debugging purposes.
     *
     * **Validates: Requirements 3.4, 3.5**
     */
    public function test_property_error_messages_include_dimension_count(): void
    {
        $testDimensions = [1, 50, 64, 127, 129, 200, 512];

        foreach ($testDimensions as $dimension) {
            $embedding = $this->generateEmbeddingWithDimensions($dimension);

            try {
                $this->calculator->validateEmbedding($embedding, 'customer');
                $this->fail("Expected InvalidEmbeddingException for dimension {$dimension}");
            } catch (InvalidEmbeddingException $e) {
                // Verify error message includes the actual dimension count
                $this->assertStringContainsString(
                    (string)$dimension,
                    $e->getMessage(),
                    "Error message should include actual dimension count {$dimension}"
                );

                // Verify error message includes expected dimension count
                $this->assertStringContainsString(
                    '128',
                    $e->getMessage(),
                    "Error message should include expected dimension count 128"
                );

                // Verify context includes dimension information
                $context = $e->getContext();
                $this->assertArrayHasKey('actual_dimensions', $context);
                $this->assertEquals($dimension, $context['actual_dimensions']);
                $this->assertEquals(128, $context['expected_dimensions']);
            }
        }
    }

    /**
     * Property: Context-aware validation messages
     * 
     * Error messages must include context information (customer vs photo)
     * to help identify which embedding caused the validation failure.
     *
     * **Validates: Requirements 3.2, 3.3**
     */
    public function test_property_context_aware_validation_messages(): void
    {
        $embedding = $this->generateEmbeddingWithDimensions(64);
        $contexts = ['customer', 'photo', 'test'];

        foreach ($contexts as $context) {
            try {
                $this->calculator->validateEmbedding($embedding, $context);
                $this->fail("Expected InvalidEmbeddingException for context {$context}");
            } catch (InvalidEmbeddingException $e) {
                // Verify context is included in exception context array
                $exceptionContext = $e->getContext();
                $this->assertArrayHasKey('context', $exceptionContext);
                $this->assertEquals($context, $exceptionContext['context']);
            }
        }
    }

    /**
     * Property: Validation works for all numeric types
     * 
     * Validation must accept embeddings with various numeric types
     * (int, float) as long as they are numeric and have 128 dimensions.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_property_validation_accepts_various_numeric_types(): void
    {
        // Create embedding with mixed numeric types
        $embedding = [];
        for ($i = 0; $i < 128; $i++) {
            if ($i % 3 === 0) {
                $embedding[] = (int)$i;  // Integer
            } elseif ($i % 3 === 1) {
                $embedding[] = (float)$i / 10.0;  // Float
            } else {
                $embedding[] = (string)($i / 100.0);  // Numeric string
            }
        }

        // Should not throw exception
        $this->calculator->validateEmbedding($embedding, 'test');
        $this->assertTrue(true);
    }

    /**
     * Property: Validation rejects non-numeric values
     * 
     * Embeddings containing non-numeric values must be rejected
     * with appropriate error message.
     *
     * **Validates: Requirements 3.2, 9.3**
     */
    public function test_property_validation_rejects_non_numeric_values(): void
    {
        $nonNumericValues = ['string', null, true, false, [], new \stdClass()];

        foreach ($nonNumericValues as $nonNumericValue) {
            $embedding = array_fill(0, 128, 0.5);
            $embedding[50] = $nonNumericValue;

            try {
                $this->calculator->validateEmbedding($embedding, 'test');
                $this->fail("Expected InvalidEmbeddingException for non-numeric value");
            } catch (InvalidEmbeddingException $e) {
                $this->assertStringContainsString(
                    'numeric',
                    strtolower($e->getMessage()),
                    "Error message should mention numeric requirement"
                );
            }
        }
    }

    /**
     * Property: Validation is consistent across multiple calls
     * 
     * Calling validation multiple times with the same embedding
     * must produce consistent results (deterministic behavior).
     *
     * **Validates: Requirements 3.1, 3.4**
     */
    public function test_property_validation_is_deterministic(): void
    {
        $validEmbedding = $this->generateEmbeddingWithDimensions(128);
        $invalidEmbedding = $this->generateEmbeddingWithDimensions(64);

        // Valid embedding should always pass
        for ($i = 0; $i < 5; $i++) {
            $this->calculator->validateEmbedding($validEmbedding, 'test');
        }

        // Invalid embedding should always fail
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->calculator->validateEmbedding($invalidEmbedding, 'test');
                $this->fail("Expected InvalidEmbeddingException on iteration {$i}");
            } catch (InvalidEmbeddingException $e) {
                // Expected
            }
        }

        $this->assertTrue(true);
    }
}
