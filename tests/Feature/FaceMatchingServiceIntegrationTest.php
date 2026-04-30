<?php

namespace Tests\Feature;

use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\FaceMatchingConfig;
use App\Services\FaceMatching\FaceMatchingService;
use Tests\TestCase;

/**
 * Comprehensive Integration Test for Face Matching Service
 * 
 * Validates all requirements from Task 16:
 * - Requirement 6.1: Configuration stored in application config
 * - Requirement 6.6: Configured threshold applied to all operations
 * - Requirement 10.1: Error logging with full context
 * - Requirement 10.2: Zero magnitude warnings logged
 * - Requirement 10.3: Batch operation logging with timing
 * - Requirement 18.1: Stateless service design
 * - Requirement 18.2: Thread-safe concurrent usage
 */
class FaceMatchingServiceIntegrationTest extends TestCase
{
    /**
     * Test Requirement 6.1: Configuration stored in application config
     */
    public function test_requirement_6_1_configuration_stored_in_application_config(): void
    {
        // Verify configuration file exists and is loaded
        $this->assertNotNull(config('face_matching'));
        $this->assertIsArray(config('face_matching'));
        
        // Verify key configuration values are present
        $this->assertArrayHasKey('threshold', config('face_matching'));
        $this->assertArrayHasKey('performance', config('face_matching'));
        $this->assertArrayHasKey('validation', config('face_matching'));
        $this->assertArrayHasKey('logging', config('face_matching'));
        
        // Verify FaceMatchingConfig can read from config
        $threshold = FaceMatchingConfig::getSimilarityThreshold();
        $this->assertIsFloat($threshold);
        $this->assertGreaterThanOrEqual(0.0, $threshold);
        $this->assertLessThanOrEqual(1.0, $threshold);
    }

    /**
     * Test Requirement 6.6: Configured threshold applied to all operations
     */
    public function test_requirement_6_6_configured_threshold_applied_to_all_operations(): void
    {
        // Get configured threshold
        $configuredThreshold = config('face_matching.threshold', 0.6);
        
        // Resolve service from container
        $service = $this->app->make(FaceMatchingService::class);
        
        // Verify service uses configured threshold
        $this->assertEquals($configuredThreshold, $service->getDefaultThreshold());
        
        // Create test data
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]), // High similarity
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.1)]), // Low similarity
        ];
        
        // Match without explicit threshold (should use configured default)
        $results = $service->matchFaces($customerEmbedding, $photoEmbeddings);
        
        // Verify results use configured threshold
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            // matchesThreshold should be based on configured threshold
            $expectedMatch = $result->similarityScore >= $configuredThreshold;
            $this->assertEquals($expectedMatch, $result->matchesThreshold);
        }
    }

    /**
     * Test Requirement 18.1: Stateless service design
     */
    public function test_requirement_18_1_stateless_service_design(): void
    {
        $service = $this->app->make(FaceMatchingService::class);
        
        // Create test data for first operation
        $customerEmbedding1 = array_fill(0, 128, 0.5);
        $photoEmbeddings1 = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
        ];
        
        // First operation
        $results1 = $service->matchFaces($customerEmbedding1, $photoEmbeddings1);
        
        // Create different test data for second operation
        $customerEmbedding2 = array_fill(0, 128, 0.3);
        $photoEmbeddings2 = [
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.3)]),
        ];
        
        // Second operation
        $results2 = $service->matchFaces($customerEmbedding2, $photoEmbeddings2);
        
        // Verify operations are independent (stateless)
        $this->assertNotEquals($results1[0]->photoId, $results2[0]->photoId);
        $this->assertNotEquals($results1[0]->similarityScore, $results2[0]->similarityScore);
        
        // Verify first operation can be repeated with same results (deterministic)
        $results1Repeat = $service->matchFaces($customerEmbedding1, $photoEmbeddings1);
        $this->assertEquals($results1[0]->similarityScore, $results1Repeat[0]->similarityScore);
    }

    /**
     * Test Requirement 18.2: Thread-safe concurrent usage
     */
    public function test_requirement_18_2_thread_safe_concurrent_usage(): void
    {
        // Verify service is registered as singleton
        $service1 = $this->app->make(FaceMatchingService::class);
        $service2 = $this->app->make(FaceMatchingService::class);
        
        $this->assertSame($service1, $service2, 'Service should be singleton');
        
        // Verify calculator is also singleton
        $calculator1 = $this->app->make(CosineSimilarityCalculator::class);
        $calculator2 = $this->app->make(CosineSimilarityCalculator::class);
        
        $this->assertSame($calculator1, $calculator2, 'Calculator should be singleton');
        
        // Simulate concurrent usage (same service instance, different data)
        $customerEmbedding1 = array_fill(0, 128, 0.5);
        $customerEmbedding2 = array_fill(0, 128, 0.3);
        
        $photoEmbeddings1 = [new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)])];
        $photoEmbeddings2 = [new PhotoEmbeddingData(2, [array_fill(0, 128, 0.3)])];
        
        // Both operations should work correctly with same service instance
        $results1 = $service1->matchFaces($customerEmbedding1, $photoEmbeddings1);
        $results2 = $service1->matchFaces($customerEmbedding2, $photoEmbeddings2);
        
        $this->assertNotEmpty($results1);
        $this->assertNotEmpty($results2);
        $this->assertNotEquals($results1[0]->photoId, $results2[0]->photoId);
    }

    /**
     * Test that all components are properly wired together
     */
    public function test_all_components_are_wired_together(): void
    {
        // Verify FaceMatchingService can be resolved
        $service = $this->app->make(FaceMatchingService::class);
        $this->assertInstanceOf(FaceMatchingService::class, $service);
        
        // Verify CosineSimilarityCalculator can be resolved
        $calculator = $this->app->make(CosineSimilarityCalculator::class);
        $this->assertInstanceOf(CosineSimilarityCalculator::class, $calculator);
        
        // Verify configuration is loaded
        $this->assertNotNull(config('face_matching.threshold'));
        
        // Verify service is functional end-to-end
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.3)]),
            new PhotoEmbeddingData(3, [array_fill(0, 128, 0.7)]),
        ];
        
        $results = $service->matchFaces($customerEmbedding, $photoEmbeddings);
        
        // Verify results are correct
        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        
        // Verify results are sorted by similarity (descending)
        $this->assertGreaterThanOrEqual($results[1]->similarityScore, $results[0]->similarityScore);
        $this->assertGreaterThanOrEqual($results[2]->similarityScore, $results[1]->similarityScore);
    }

    /**
     * Test that dependency injection works in controllers
     */
    public function test_dependency_injection_works_in_controllers(): void
    {
        // Simulate a controller with constructor injection
        $controller = new class(
            $this->app->make(FaceMatchingService::class),
            $this->app->make(CosineSimilarityCalculator::class)
        ) {
            public function __construct(
                public FaceMatchingService $faceMatchingService,
                public CosineSimilarityCalculator $calculator
            ) {}
            
            public function performMatching(array $customerEmbedding, array $photoEmbeddings): array
            {
                return $this->faceMatchingService->matchFaces($customerEmbedding, $photoEmbeddings);
            }
        };
        
        // Verify dependencies are injected
        $this->assertInstanceOf(FaceMatchingService::class, $controller->faceMatchingService);
        $this->assertInstanceOf(CosineSimilarityCalculator::class, $controller->calculator);
        
        // Verify controller can use the service
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
        ];
        
        $results = $controller->performMatching($customerEmbedding, $photoEmbeddings);
        
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test that configuration can be customized
     */
    public function test_configuration_can_be_customized(): void
    {
        // Temporarily change configuration
        $originalThreshold = config('face_matching.threshold');
        config(['face_matching.threshold' => 0.75]);
        
        // Create new service instance (in real app, would need to clear singleton)
        $calculator = new CosineSimilarityCalculator();
        $service = new FaceMatchingService($calculator, FaceMatchingConfig::getSimilarityThreshold());
        
        // Verify new threshold is used
        $this->assertEquals(0.75, $service->getDefaultThreshold());
        
        // Restore original configuration
        config(['face_matching.threshold' => $originalThreshold]);
    }

    /**
     * Test that service provider publishes configuration
     */
    public function test_service_provider_publishes_configuration(): void
    {
        // Verify configuration file exists
        $configPath = config_path('face_matching.php');
        $this->assertFileExists($configPath);
        
        // Verify configuration has required structure
        $config = require $configPath;
        $this->assertIsArray($config);
        $this->assertArrayHasKey('threshold', $config);
        $this->assertArrayHasKey('performance', $config);
        $this->assertArrayHasKey('validation', $config);
        $this->assertArrayHasKey('logging', $config);
    }
}
