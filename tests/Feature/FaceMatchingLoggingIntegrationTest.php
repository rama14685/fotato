<?php

namespace Tests\Feature;

use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\FaceMatchingService;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test Face Matching Service Logging Integration
 * 
 * Verifies that the Face Matching Service properly integrates with Laravel's
 * logging system and logs appropriate messages for various operations.
 */
class FaceMatchingLoggingIntegrationTest extends TestCase
{
    /**
     * Test that batch operations are logged
     */
    public function test_batch_operations_are_logged(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Face matching batch operation started'
                    && isset($context['photo_count'])
                    && isset($context['threshold'])
                    && isset($context['timestamp']);
            });

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Face matching batch operation completed'
                    && isset($context['photo_count'])
                    && isset($context['result_count'])
                    && isset($context['elapsed_seconds'])
                    && isset($context['timestamp']);
            });

        $service = $this->app->make(FaceMatchingService::class);
        
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.3)]),
        ];

        $service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);
    }

    /**
     * Test that chunked processing is logged for large albums
     */
    public function test_chunked_processing_is_logged_for_large_albums(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Starting chunked face matching for large album'
                    && isset($context['photo_count'])
                    && isset($context['chunk_size'])
                    && isset($context['threshold']);
            });

        Log::shouldReceive('debug')
            ->atLeast()
            ->once();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Chunked face matching completed'
                    && isset($context['photo_count'])
                    && isset($context['result_count'])
                    && isset($context['elapsed_seconds']);
            });

        $service = $this->app->make(FaceMatchingService::class);
        
        $customerEmbedding = array_fill(0, 128, 0.5);
        
        // Create 5001 photos to trigger chunked processing
        $photoEmbeddings = [];
        for ($i = 1; $i <= 5001; $i++) {
            $photoEmbeddings[] = new PhotoEmbeddingData($i, [array_fill(0, 128, 0.5)]);
        }

        $service->matchFacesChunked($customerEmbedding, $photoEmbeddings);
    }

    /**
     * Test that empty collection warnings are logged
     */
    public function test_empty_collection_warnings_are_logged(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Face matching called with empty photo collection'
                    && isset($context['timestamp']);
            });

        $service = $this->app->make(FaceMatchingService::class);
        
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [];

        $service->matchFacesWithRecovery($customerEmbedding, $photoEmbeddings);
    }

    /**
     * Test that the service can be used in a controller context
     */
    public function test_service_can_be_used_in_controller_context(): void
    {
        // Simulate a controller using dependency injection
        $controller = new class($this->app->make(FaceMatchingService::class)) {
            public function __construct(
                private FaceMatchingService $faceMatchingService
            ) {}

            public function matchFaces(array $customerEmbedding, array $photoEmbeddings): array
            {
                return $this->faceMatchingService->matchFaces($customerEmbedding, $photoEmbeddings);
            }
        };

        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
        ];

        $results = $controller->matchFaces($customerEmbedding, $photoEmbeddings);

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
    }
}
