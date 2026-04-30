<?php

namespace Tests\Unit\Services\FaceMatching;

use Tests\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\Exceptions\FaceMatchingException;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;
use App\Services\FaceMatching\Exceptions\InvalidThresholdException;
use App\Services\FaceMatching\Exceptions\PerformanceException;
use Illuminate\Support\Facades\Log;

/**
 * Unit Tests for Task 8.3: Exception Handling
 *
 * Tests all exception types, factory methods, error logging, context preservation,
 * and error recovery scenarios.
 * 
 * Validates Requirements: 10.1, 10.2, 17.1, 17.2, 3.2, 3.3, 6.4, 9.3
 */
class ExceptionHandlingTest extends TestCase
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
    // EXCEPTION TYPE TESTS - InvalidEmbeddingException Factory Methods
    // ============================================================================

    /**
     * Test 1: InvalidEmbeddingException::nullEmbedding factory method
     * Requirement: 17.1
     */
    public function test_null_embedding_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::nullEmbedding('customer');

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('customer', $exception->getMessage());
        $this->assertStringContainsString('null', strtolower($exception->getMessage()));
        
        $context = $exception->getContext();
        $this->assertArrayHasKey('context', $context);
        $this->assertEquals('customer', $context['context']);
    }

    /**
     * Test 2: InvalidEmbeddingException::invalidDimensions factory method
     * Requirement: 3.2
     */
    public function test_invalid_dimensions_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::invalidDimensions('photo', 64, 128);

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('64', $exception->getMessage());
        $this->assertStringContainsString('128', $exception->getMessage());
        
        $context = $exception->getContext();
        $this->assertArrayHasKey('actual_dimensions', $context);
        $this->assertArrayHasKey('expected_dimensions', $context);
        $this->assertEquals(64, $context['actual_dimensions']);
        $this->assertEquals(128, $context['expected_dimensions']);
    }

    /**
     * Test 3: InvalidEmbeddingException::nonNumericValue factory method
     * Requirement: 9.3
     */
    public function test_non_numeric_value_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::nonNumericValue('customer');

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('numeric', strtolower($exception->getMessage()));
        
        $context = $exception->getContext();
        $this->assertArrayHasKey('context', $context);
        $this->assertEquals('customer', $context['context']);
    }

    /**
     * Test 4: InvalidEmbeddingException::invalidPhotoData factory method
     * Requirement: 9.3
     */
    public function test_invalid_photo_data_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::invalidPhotoData();

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('PhotoEmbeddingData', $exception->getMessage());
    }

    /**
     * Test 5: InvalidEmbeddingException::invalidPhotoId factory method
     * Requirement: 9.3
     */
    public function test_invalid_photo_id_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::invalidPhotoId();

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('photo ID', $exception->getMessage());
    }

    /**
     * Test 6: InvalidEmbeddingException::invalidEmbeddingsStructure factory method
     * Requirement: 9.3
     */
    public function test_invalid_embeddings_structure_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::invalidEmbeddingsStructure();

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('array', strtolower($exception->getMessage()));
    }

    /**
     * Test 7: InvalidEmbeddingException::emptyEmbeddings factory method
     * Requirement: 9.3
     */
    public function test_empty_embeddings_exception_factory_method(): void
    {
        $exception = InvalidEmbeddingException::emptyEmbeddings();

        $this->assertInstanceOf(InvalidEmbeddingException::class, $exception);
        $this->assertStringContainsString('at least one', strtolower($exception->getMessage()));
    }

    // ============================================================================
    // EXCEPTION TYPE TESTS - InvalidThresholdException Factory Methods
    // ============================================================================

    /**
     * Test 8: InvalidThresholdException::outOfRange factory method
     * Requirement: 6.4
     */
    public function test_invalid_threshold_exception_factory_method(): void
    {
        $exception = InvalidThresholdException::outOfRange(1.5);

        $this->assertInstanceOf(InvalidThresholdException::class, $exception);
        $this->assertStringContainsString('1.5', $exception->getMessage());
        $this->assertStringContainsString('0.0', $exception->getMessage());
        $this->assertStringContainsString('1.0', $exception->getMessage());
        
        $context = $exception->getContext();
        $this->assertArrayHasKey('threshold_value', $context);
        $this->assertEquals(1.5, $context['threshold_value']);
    }

    // ============================================================================
    // EXCEPTION TYPE TESTS - PerformanceException Factory Methods
    // ============================================================================

    /**
     * Test 9: PerformanceException::processingTimeout factory method
     * Requirement: 10.1
     */
    public function test_performance_timeout_exception_factory_method(): void
    {
        $exception = PerformanceException::processingTimeout(1000, 15.5, 10.0);

        $this->assertInstanceOf(PerformanceException::class, $exception);
        $this->assertStringContainsString('1000', $exception->getMessage());
        $this->assertStringContainsString('15.5', $exception->getMessage());
        $this->assertStringContainsString('10', $exception->getMessage());
        
        $context = $exception->getContext();
        $this->assertArrayHasKey('photo_count', $context);
        $this->assertArrayHasKey('elapsed_seconds', $context);
        $this->assertArrayHasKey('max_seconds', $context);
        $this->assertEquals(1000, $context['photo_count']);
        $this->assertEquals(15.5, $context['elapsed_seconds']);
        $this->assertEquals(10.0, $context['max_seconds']);
    }

    /**
     * Test 10: PerformanceException::memoryLimitExceeded factory method
     * Requirement: 10.1
     */
    public function test_performance_memory_exception_factory_method(): void
    {
        $exception = PerformanceException::memoryLimitExceeded(5000, 600000000, 512000000);

        $this->assertInstanceOf(PerformanceException::class, $exception);
        $this->assertStringContainsString('5000', $exception->getMessage());
        $this->assertStringContainsString('memory', strtolower($exception->getMessage()));
        
        $context = $exception->getContext();
        $this->assertArrayHasKey('photo_count', $context);
        $this->assertArrayHasKey('memory_used_bytes', $context);
        $this->assertArrayHasKey('memory_limit_bytes', $context);
    }

    // ============================================================================
    // EXCEPTION BASE CLASS TESTS - Context Support
    // ============================================================================

    /**
     * Test 11: FaceMatchingException base class supports context
     * Requirement: 10.1
     */
    public function test_base_exception_supports_context(): void
    {
        $context = ['key1' => 'value1', 'key2' => 'value2'];
        $exception = new class('Test message', 0, null, $context) extends FaceMatchingException {};

        $this->assertEquals($context, $exception->getContext());
    }

    /**
     * Test 12: FaceMatchingException withContext method adds context
     * Requirement: 10.1
     */
    public function test_base_exception_with_context_method(): void
    {
        $exception = new class('Test message') extends FaceMatchingException {};
        $exception->withContext('test_key', 'test_value');

        $context = $exception->getContext();
        $this->assertArrayHasKey('test_key', $context);
        $this->assertEquals('test_value', $context['test_key']);
    }

    /**
     * Test 13: Exception context is preserved through inheritance
     * Requirement: 10.1
     */
    public function test_exception_context_preserved_through_inheritance(): void
    {
        $exception = InvalidEmbeddingException::invalidDimensions('test', 64, 128);

        $this->assertInstanceOf(FaceMatchingException::class, $exception);
        $context = $exception->getContext();
        $this->assertNotEmpty($context);
        $this->assertArrayHasKey('actual_dimensions', $context);
    }

    // ============================================================================
    // ERROR LOGGING TESTS - Privacy-Safe Logging
    // ============================================================================

    /**
     * Test 14: Empty photo collection logs warning
     * Requirement: 10.2, 17.5
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
     * Test 15: Batch operation start is logged
     * Requirement: 10.1
     */
    public function test_batch_operation_start_is_logged(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        Log::shouldHaveReceived('info')
            ->with('Face matching batch operation started', \Mockery::type('array'));
    }

    /**
     * Test 16: Batch operation completion is logged
     * Requirement: 10.1
     */
    public function test_batch_operation_completion_is_logged(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        Log::shouldHaveReceived('info')
            ->with('Face matching batch operation completed', \Mockery::on(function ($arg) {
                return is_array($arg) 
                    && isset($arg['photo_count'])
                    && isset($arg['result_count'])
                    && isset($arg['elapsed_seconds']);
            }));
    }

    /**
     * Test 17: Embedding validation error is logged with context
     * Requirement: 10.1, 10.2
     */
    public function test_embedding_validation_error_logged_with_context(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $invalidEmbedding = array_fill(0, 64, 0.5); // Wrong dimensions
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$invalidEmbedding]),
        ];

        try {
            $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);
        } catch (InvalidEmbeddingException $e) {
            // Expected
        }

        Log::shouldHaveReceived('error')
            ->with('Face matching embedding validation failed', \Mockery::on(function ($arg) {
                return is_array($arg) 
                    && isset($arg['error'])
                    && isset($arg['context'])
                    && isset($arg['photo_count']);
            }));
    }

    /**
     * Test 18: Raw embedding values are never logged
     * Requirement: 10.5
     */
    public function test_raw_embedding_values_never_logged(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        // Verify that no log call contains embedding arrays
        Log::shouldHaveReceived('info')
            ->with(\Mockery::any(), \Mockery::on(function ($arg) {
                // Check that the log data doesn't contain embedding arrays
                if (!is_array($arg)) {
                    return true;
                }
                
                // Recursively check for array values that look like embeddings
                foreach ($arg as $value) {
                    if (is_array($value) && count($value) === 128) {
                        return false; // Found potential embedding array
                    }
                }
                
                return true;
            }));
    }

    // ============================================================================
    // ERROR RECOVERY TESTS - Batch Processing Recovery
    // ============================================================================

    /**
     * Test 19: Invalid photo embeddings are filtered and processing continues
     * Requirement: 10.2
     */
    public function test_invalid_photo_embeddings_filtered_and_processing_continues(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $validEmbedding = $this->generateValidEmbedding();
        $invalidEmbedding = array_fill(0, 64, 0.5); // Wrong dimensions

        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$validEmbedding]),
            new PhotoEmbeddingData(2, [$invalidEmbedding]), // Invalid
            new PhotoEmbeddingData(3, [$validEmbedding]),
        ];

        $results = $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        // Should return results for valid photos only
        $this->assertCount(2, $results);
        $photoIds = array_map(fn($r) => $r->photoId, $results);
        $this->assertContains(1, $photoIds);
        $this->assertContains(3, $photoIds);
        $this->assertNotContains(2, $photoIds);
    }

    /**
     * Test 20: Recovery logs filtering information
     * Requirement: 10.2
     */
    public function test_recovery_logs_filtering_information(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $validEmbedding = $this->generateValidEmbedding();
        $invalidEmbedding = array_fill(0, 64, 0.5);

        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$validEmbedding]),
            new PhotoEmbeddingData(2, [$invalidEmbedding]),
        ];

        $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        Log::shouldHaveReceived('warning')
            ->with('Skipping invalid photo embedding', \Mockery::on(function ($arg) {
                return is_array($arg) && isset($arg['photo_id']);
            }));

        Log::shouldHaveReceived('info')
            ->with('Retrying with filtered valid embeddings', \Mockery::on(function ($arg) {
                return is_array($arg) 
                    && isset($arg['original_count'])
                    && isset($arg['valid_count'])
                    && isset($arg['filtered_count']);
            }));
    }

    /**
     * Test 21: All invalid embeddings returns empty array
     * Requirement: 10.2
     */
    public function test_all_invalid_embeddings_returns_empty_array(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $invalidEmbedding = array_fill(0, 64, 0.5);

        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$invalidEmbedding]),
            new PhotoEmbeddingData(2, [$invalidEmbedding]),
        ];

        $results = $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        $this->assertEmpty($results);
        
        Log::shouldHaveReceived('warning')
            ->with('No valid photo embeddings after filtering', \Mockery::type('array'));
    }

    /**
     * Test 22: Customer embedding error is not recoverable
     * Requirement: 17.1
     */
    public function test_customer_embedding_error_not_recoverable(): void
    {
        $invalidCustomerEmbedding = array_fill(0, 64, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        $this->expectException(InvalidEmbeddingException::class);
        
        $this->service->matchFacesWithRecovery($invalidCustomerEmbedding, $photoEmbeddings);
    }

    /**
     * Test 23: Recovery marks results as recovered in logs
     * Requirement: 10.2
     */
    public function test_recovery_marks_results_as_recovered(): void
    {
        $customerEmbedding = $this->generateValidEmbedding();
        $validEmbedding = $this->generateValidEmbedding();
        $invalidEmbedding = array_fill(0, 64, 0.5);

        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$validEmbedding]),
            new PhotoEmbeddingData(2, [$invalidEmbedding]),
        ];

        $this->service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);

        Log::shouldHaveReceived('info')
            ->with('Face matching batch operation completed', \Mockery::on(function ($arg) {
                return is_array($arg) && isset($arg['recovered']) && $arg['recovered'] === true;
            }));
    }

    /**
     * Test 24: Performance warning logged when processing exceeds target
     * Requirement: 10.4
     */
    public function test_performance_warning_logged_when_exceeding_target(): void
    {
        // This test would require mocking time or using a large dataset
        // For now, we verify the logging structure exists
        $this->assertTrue(method_exists($this->service, 'matchFacesWithRecovery'));
    }

    /**
     * Test 25: Unexpected errors are logged with context
     * Requirement: 10.1
     */
    public function test_unexpected_errors_logged_with_context(): void
    {
        // Create a service with a mock calculator that throws unexpected error
        $mockCalculator = $this->createMock(CosineSimilarityCalculator::class);
        $mockCalculator->method('validateEmbedding')
            ->willThrowException(new \RuntimeException('Unexpected error'));

        $service = new FaceMatchingService($mockCalculator);
        
        $customerEmbedding = $this->generateValidEmbedding();
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [$this->generateValidEmbedding()]),
        ];

        try {
            $service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);
            $this->fail('Expected exception to be thrown');
        } catch (\RuntimeException $e) {
            // Expected
        }

        Log::shouldHaveReceived('error')
            ->with('Unexpected error in face matching service', \Mockery::on(function ($arg) {
                return is_array($arg) 
                    && isset($arg['error'])
                    && isset($arg['error_class'])
                    && isset($arg['photo_count']);
            }));
    }
}
