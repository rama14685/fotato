<?php

namespace Tests\Feature;

use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\FaceMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Face Matching Service Provider Integration
 * 
 * Verifies that the FaceMatchingServiceProvider correctly registers services
 * in Laravel's service container and that dependency injection works as expected.
 */
class FaceMatchingServiceProviderTest extends TestCase
{
    /**
     * Test that FaceMatchingService can be resolved from the container
     */
    public function test_face_matching_service_can_be_resolved_from_container(): void
    {
        $service = $this->app->make(FaceMatchingService::class);
        
        $this->assertInstanceOf(FaceMatchingService::class, $service);
    }

    /**
     * Test that CosineSimilarityCalculator can be resolved from the container
     */
    public function test_cosine_similarity_calculator_can_be_resolved_from_container(): void
    {
        $calculator = $this->app->make(CosineSimilarityCalculator::class);
        
        $this->assertInstanceOf(CosineSimilarityCalculator::class, $calculator);
    }

    /**
     * Test that FaceMatchingService is registered as singleton
     */
    public function test_face_matching_service_is_singleton(): void
    {
        $service1 = $this->app->make(FaceMatchingService::class);
        $service2 = $this->app->make(FaceMatchingService::class);
        
        $this->assertSame($service1, $service2, 'FaceMatchingService should be registered as singleton');
    }

    /**
     * Test that CosineSimilarityCalculator is registered as singleton
     */
    public function test_cosine_similarity_calculator_is_singleton(): void
    {
        $calculator1 = $this->app->make(CosineSimilarityCalculator::class);
        $calculator2 = $this->app->make(CosineSimilarityCalculator::class);
        
        $this->assertSame($calculator1, $calculator2, 'CosineSimilarityCalculator should be registered as singleton');
    }

    /**
     * Test that FaceMatchingService loads default threshold from config
     */
    public function test_face_matching_service_loads_default_threshold_from_config(): void
    {
        $expectedThreshold = config('face_matching.threshold', 0.6);
        
        $service = $this->app->make(FaceMatchingService::class);
        
        $this->assertEquals($expectedThreshold, $service->getDefaultThreshold());
    }

    /**
     * Test that configuration is properly loaded
     */
    public function test_configuration_is_loaded(): void
    {
        $this->assertNotNull(config('face_matching.threshold'));
        $this->assertIsFloat(config('face_matching.threshold'));
        $this->assertGreaterThanOrEqual(0.0, config('face_matching.threshold'));
        $this->assertLessThanOrEqual(1.0, config('face_matching.threshold'));
    }

    /**
     * Test that FaceMatchingService can be dependency injected
     */
    public function test_face_matching_service_can_be_dependency_injected(): void
    {
        // Create a test controller that uses dependency injection
        $testController = new class($this->app->make(FaceMatchingService::class)) {
            public function __construct(
                public FaceMatchingService $faceMatchingService
            ) {}
        };
        
        $this->assertInstanceOf(FaceMatchingService::class, $testController->faceMatchingService);
    }

    /**
     * Test that the service is functional after resolution
     */
    public function test_resolved_service_is_functional(): void
    {
        $service = $this->app->make(FaceMatchingService::class);
        
        // Create test embeddings
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [];
        
        // Should not throw exception and return empty array for empty photo collection
        $results = $service->matchFaces($customerEmbedding, $photoEmbeddings);
        
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
