<?php

namespace Tests\Unit\Services\FaceMatching;

use Tests\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;
use Illuminate\Support\Facades\Log;

/**
 * Unit Tests for Task 10.2: Null and Empty Input Handling
 *
 * Tests comprehensive null input validation and empty collection handling
 * as specified in Requirements 17.1, 17.2, 17.3, 17.4, 17.5
 */
class NullAndEmptyInputHandlingTest extends TestCase
{
    private FaceMatchingService $service;
    private CosineSimilarityCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CosineSimilarityCalculator();
        $this->service = new FaceMatchingService($this->calculator);
        Log::spy();
    }

    /**
     * Helper: Generate a valid 128-dimensional embedding
     */
    private function generateValidEmbedding(): array
    {
        return array_fill(0, 128, 0.5);
    }

    // ============================================================================
    // NULL CUSTOMER EMBEDDING TESTS - Requirement 17.1
    // ============================================================================

    /**
     * Test 1: Null customer embedding throws InvalidEmbeddingException
     * Requirement: 17.1
     */
    public function test_null_customer_embedding_throws_exception(): void
    {
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Customer embedding cannot be null');

        $this->service->matchFaces(null, $photoEmbeddings);
    }

    /**
     * Test 2: Null customer embedding exception message is correct
     * Requirement: 17.1
     */
    public function test_null_customer_embedding_exception_message(): void
    {
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        try {
            $this->service->matchFaces(null, $photoEmbeddings);
            $this->fail('Expected InvalidEmbeddingException to be thrown');
        } catch (InvalidEmbeddingException $e) {
            $this->assertEquals('Customer embedding cannot be null', $e->getMessage());
        }
    }

    /**
     * Test 3: Null customer embedding exception has correct context
     * Requirement: 17.1
     */
    public function test_null_customer_embedding_exception_context(): void
    {
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        try {
            $this->service->matchFaces(null, $photoEmbeddings);
            $this->fail('Expected InvalidEmbeddingException to be thrown');
        } catch (InvalidEmbeddingException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('context', $context);
            $this->assertEquals('Customer', $context['context']);
        }
    }

    /**
     * Test 4: Null customer embedding in matchFacesChunked throws exception
     * Requirement: 17.1
     */
    public function test_null_customer_embedding_in_chunked_throws_exception(): void
    {
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Customer embedding cannot be null');

        $this->service->matchFacesChunked(null, $photoEmbeddings);
    }

    /**
     * Test 5: Null customer embedding in matchFacesWithRecovery throws exception
     * Requirement: 17.1
     */
    public function test_null_customer_embedding_in_recovery_throws_exception(): void
    {
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Customer embedding cannot be null');

        $this->service->matchFacesWithRecovery(null, $photoEmbeddings);
    }

    // ============================================================================
    // NULL PHOTO EMBEDDINGS COLLECTION TESTS - Requirement 17.2
    // ============================================================================

    /**
     * Test 6: Null photo embeddings collection throws InvalidEmbeddingException
     * Requirement: 17.2
     */
    public function test_null_photo_embeddings_collection_throws_exception(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();

        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Photo embeddings collection cannot be null');

        $this->service->matchFaces($customerEmbedding, null);
    }

    /**
     * Test 7: Null photo embeddings collection exception message is correct
     * Requirement: 17.2
     */
    public function test_null_photo_embeddings_collection_exception_message(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();

        try {
            $this->service->matchFaces($customerEmbedding, null);
            $this->fail('Expected InvalidEmbeddingException to be thrown');
        } catch (InvalidEmbeddingException $e) {
            $this->assertEquals('Photo embeddings collection cannot be null', $e->getMessage());
        }
    }

    /**
     * Test 8: Null photo embeddings collection exception has correct context
     * Requirement: 17.2
     */
    public function test_null_photo_embeddings_collection_exception_context(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();

        try {
            $this->service->matchFaces($customerEmbedding, null);
            $this->fail('Expected InvalidEmbeddingException to be thrown');
        } catch (InvalidEmbeddingException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('context', $context);
            $this->assertEquals('Photo embeddings collection', $context['context']);
        }
    }

    /**
     * Test 9: Null photo embeddings in matchFacesChunked throws exception
     * Requirement: 17.2
     */
    public function test_null_photo_embeddings_in_chunked_throws_exception(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();

        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Photo embeddings collection cannot be null');

        $this->service->matchFacesChunked($customerEmbedding, null);
    }

    /**
     * Test 10: Null photo embeddings in matchFacesWithRecovery throws exception
     * Requirement: 17.2
     */
    public function test_null_photo_embeddings_in_recovery_throws_exception(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();

        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Photo embeddings collection cannot be null');

        $this->service->matchFacesWithRecovery($customerEmbedding, null);
    }

    /**
     * Test 11: Both null inputs throws exception for customer embedding first
     * Requirement: 17.1, 17.2
     */
    public function test_both_null_inputs_throws_customer_exception_first(): void
    {
        // Customer embedding is checked first, so that exception should be thrown
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Customer embedding cannot be null');

        $this->service->matchFaces(null, null);
    }

    // ============================================================================
    // EMPTY PHOTO COLLECTION TESTS - Requirements 17.3, 17.4, 17.5
    // ============================================================================

    /**
     * Test 12: Empty photo collection returns empty array
     * Requirement: 17.3
     */
    public function test_empty_photo_collection_returns_empty_array(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result = $this->service->matchFaces($customerEmbedding, $emptyPhotos);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
        $this->assertCount(0, $result);
    }

    /**
     * Test 13: Empty photo collection does not throw exception
     * Requirement: 17.4
     */
    public function test_empty_photo_collection_does_not_throw_exception(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        // Should not throw any exception
        $result = $this->service->matchFaces($customerEmbedding, $emptyPhotos);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test 14: Empty photo collection logs warning in matchFacesWithRecovery
     * Requirement: 17.5
     */
    public function test_empty_photo_collection_logs_warning(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result = $this->service->matchFacesWithRecovery($customerEmbedding, $emptyPhotos);

        $this->assertEmpty($result);
        
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Face matching called with empty photo collection', \Mockery::type('array'));
    }

    /**
     * Test 15: Empty photo collection in matchFacesChunked returns empty array
     * Requirement: 17.3
     */
    public function test_empty_photo_collection_in_chunked_returns_empty_array(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result = $this->service->matchFacesChunked($customerEmbedding, $emptyPhotos);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test 16: Empty photo collection in matchFacesWithRecovery returns empty array
     * Requirement: 17.3
     */
    public function test_empty_photo_collection_in_recovery_returns_empty_array(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result = $this->service->matchFacesWithRecovery($customerEmbedding, $emptyPhotos);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test 17: Empty photo collection in matchFacesWithRecovery logs warning
     * Requirement: 17.5
     */
    public function test_empty_photo_collection_in_recovery_logs_warning(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result = $this->service->matchFacesWithRecovery($customerEmbedding, $emptyPhotos);

        $this->assertEmpty($result);
        
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Face matching called with empty photo collection', \Mockery::type('array'));
    }

    // ============================================================================
    // EDGE CASE TESTS - Combined scenarios
    // ============================================================================

    /**
     * Test 18: Valid customer embedding with empty collection returns empty array
     * Requirement: 17.3, 17.4
     */
    public function test_valid_customer_with_empty_collection(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result = $this->service->matchFaces($customerEmbedding, $emptyPhotos);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test 19: Empty collection with custom threshold returns empty array
     * Requirement: 17.3
     */
    public function test_empty_collection_with_custom_threshold(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];
        $customThreshold = 0.8;

        $result = $this->service->matchFaces($customerEmbedding, $emptyPhotos, $customThreshold);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test 20: Null customer embedding takes precedence over empty collection
     * Requirement: 17.1
     */
    public function test_null_customer_takes_precedence_over_empty_collection(): void
    {
        $emptyPhotos = [];

        // Should throw exception for null customer, not return empty array for empty collection
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Customer embedding cannot be null');

        $this->service->matchFaces(null, $emptyPhotos);
    }

    /**
     * Test 21: Null photo collection takes precedence over invalid customer
     * Requirement: 17.2
     */
    public function test_null_photo_collection_checked_after_customer(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();

        // Should throw exception for null photo collection
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Photo embeddings collection cannot be null');

        $this->service->matchFaces($customerEmbedding, null);
    }

    /**
     * Test 22: Empty collection warning includes timestamp in matchFacesWithRecovery
     * Requirement: 17.5
     */
    public function test_empty_collection_warning_includes_timestamp(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $this->service->matchFacesWithRecovery($customerEmbedding, $emptyPhotos);

        Log::shouldHaveReceived('warning')
            ->with('Face matching called with empty photo collection', \Mockery::on(function ($arg) {
                return is_array($arg) && isset($arg['timestamp']);
            }));
    }

    /**
     * Test 23: Multiple calls with empty collection all return empty arrays
     * Requirement: 17.3
     */
    public function test_multiple_calls_with_empty_collection(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $emptyPhotos = [];

        $result1 = $this->service->matchFaces($customerEmbedding, $emptyPhotos);
        $result2 = $this->service->matchFaces($customerEmbedding, $emptyPhotos);
        $result3 = $this->service->matchFaces($customerEmbedding, $emptyPhotos);

        $this->assertEmpty($result1);
        $this->assertEmpty($result2);
        $this->assertEmpty($result3);
    }

    /**
     * Test 24: Empty collection does not validate customer embedding dimensions
     * Requirement: 17.3 - Early return optimization
     */
    public function test_empty_collection_skips_customer_validation(): void
    {
        // Even with invalid customer embedding dimensions, empty collection returns empty array
        $invalidCustomerEmbedding = array_fill(0, 64, 0.5); // Wrong dimensions
        $emptyPhotos = [];

        $result = $this->service->matchFaces($invalidCustomerEmbedding, $emptyPhotos);

        $this->assertEmpty($result);
    }

    /**
     * Test 25: Null inputs are checked before empty collection
     * Requirement: 17.1, 17.2, 17.3 - Validation order
     */
    public function test_null_checks_before_empty_check(): void
    {
        // Null customer should throw exception even if photo collection would be empty
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Customer embedding cannot be null');

        $this->service->matchFaces(null, []);
    }
}
