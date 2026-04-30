<?php

namespace App\Providers;

use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\FaceMatchingConfig;
use App\Services\FaceMatching\FaceMatchingService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Face Matching Service
 * 
 * Registers the Face Matching Service and its dependencies in Laravel's service container.
 * Configures singleton instances for performance and thread-safe concurrent usage.
 * Provides configuration publishing for customization.
 */
class FaceMatchingServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container
     * 
     * Registers the Face Matching Service and Cosine Similarity Calculator as singletons.
     * Both services are stateless and thread-safe, making them safe to share across requests.
     * 
     * @return void
     */
    public function register(): void
    {
        // Register Cosine Similarity Calculator as singleton
        // This is a pure mathematical calculator with no state - safe to share
        $this->app->singleton(CosineSimilarityCalculator::class, function ($app) {
            return new CosineSimilarityCalculator();
        });

        // Register Face Matching Service as singleton
        // The service is stateless and thread-safe, so a single instance can handle multiple requests
        $this->app->singleton(FaceMatchingService::class, function ($app) {
            $calculator = $app->make(CosineSimilarityCalculator::class);
            $defaultThreshold = FaceMatchingConfig::getSimilarityThreshold();
            
            return new FaceMatchingService($calculator, $defaultThreshold);
        });
    }

    /**
     * Bootstrap services
     * 
     * Publishes configuration files and sets up any runtime configuration.
     * 
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration file for customization
        $this->publishes([
            __DIR__.'/../../config/face_matching.php' => config_path('face_matching.php'),
        ], 'face-matching-config');

        // Merge default configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/face_matching.php',
            'face_matching'
        );
    }
}
