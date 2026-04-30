<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;

/**
 * Unit tests for CosineSimilarityCalculator
 */
class CosineSimilarityCalculatorTest extends TestCase
{
    private CosineSimilarityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CosineSimilarityCalculator();
    }

    /**
     * Test that calculator can be instantiated
     */
    public function test_calculator_instantiation(): void
    {
        $this->assertInstanceOf(CosineSimilarityCalculator::class, $this->calculator);
    }

    /**
     * Test dot product calculation
     */
    public function test_dot_product_calculation(): void
    {
        $vectorA = [1.0, 2.0, 3.0];
        $vectorB = [4.0, 5.0, 6.0];

        // Expected: (1*4) + (2*5) + (3*6) = 4 + 10 + 18 = 32
        $result = $this->calculator->dotProduct($vectorA, $vectorB);

        $this->assertEquals(32.0, $result);
    }

    /**
     * Test magnitude calculation
     */
    public function test_magnitude_calculation(): void
    {
        $vector = [3.0, 4.0];

        // Expected: sqrt(3^2 + 4^2) = sqrt(9 + 16) = sqrt(25) = 5
        $result = $this->calculator->magnitude($vector);

        $this->assertEquals(5.0, $result);
    }

    /**
     * Test embedding validation with correct dimensions
     */
    public function test_validate_embedding_correct_dimensions(): void
    {
        $embedding = array_fill(0, 128, 0.5);

        // Should not throw exception
        $this->calculator->validateEmbedding($embedding, 'test');
        $this->assertTrue(true);
    }

    /**
     * Test embedding validation with incorrect dimensions
     */
    public function test_validate_embedding_incorrect_dimensions(): void
    {
        $embedding = array_fill(0, 64, 0.5);

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }

    /**
     * Test embedding validation with non-numeric values
     */
    public function test_validate_embedding_non_numeric_values(): void
    {
        $embedding = array_fill(0, 128, 0.5);
        $embedding[50] = 'invalid';

        $this->expectException(InvalidEmbeddingException::class);
        $this->calculator->validateEmbedding($embedding, 'test');
    }
}
