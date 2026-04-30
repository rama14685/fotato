<?php

namespace Tests\Unit\Services\FaceMatching;

use App\Services\FaceMatching\FaceMatchingConfig;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Property 13: Configuration Consistency
 * 
 * **Validates: Requirements 6.6**
 * 
 * This property test verifies that the configured threshold is applied
 * consistently across all operations and that configuration changes are
 * properly reflected.
 * 
 * Property: The threshold returned by getSimilarityThreshold() must always
 * match the configured value in the Laravel config system.
 */
class ConfigurationConsistencyPropertyTest extends TestCase
{
    /**
     * Store original config value to restore after tests
     */
    protected float $originalThreshold;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Store original threshold to restore later
        $this->originalThreshold = config('face_matching.threshold', 0.6);
    }

    /**
     * Restore original config after tests
     */
    protected function tearDown(): void
    {
        // Restore original threshold
        Config::set('face_matching.threshold', $this->originalThreshold);
        
        parent::tearDown();
    }

    /**
     * Property: getSimilarityThreshold returns configured value
     * 
     * For all valid threshold values set in config, getSimilarityThreshold()
     * must return that exact value.
     * 
     * @test
     */
    public function property_get_similarity_threshold_returns_configured_value(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random valid threshold
            $configuredThreshold = mt_rand(0, 1000) / 1000.0;
            
            // Set the threshold in config
            Config::set('face_matching.threshold', $configuredThreshold);
            
            // Retrieve threshold via FaceMatchingConfig
            $retrievedThreshold = FaceMatchingConfig::getSimilarityThreshold();
            
            // Verify consistency
            $this->assertEquals(
                $configuredThreshold,
                $retrievedThreshold,
                "getSimilarityThreshold() should return the configured value. " .
                "Expected: {$configuredThreshold}, Got: {$retrievedThreshold}"
            );
        }
        
        $this->assertTrue(true, "All {$iterations} configured thresholds were consistently retrieved");
    }

    /**
     * Property: Configuration changes are immediately reflected
     * 
     * When config value changes, getSimilarityThreshold() must immediately
     * return the new value.
     * 
     * @test
     */
    public function property_configuration_changes_are_immediately_reflected(): void
    {
        $iterations = 25;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Set first threshold
            $threshold1 = mt_rand(0, 500) / 1000.0;
            Config::set('face_matching.threshold', $threshold1);
            $retrieved1 = FaceMatchingConfig::getSimilarityThreshold();
            
            $this->assertEquals(
                $threshold1,
                $retrieved1,
                "First threshold should be retrieved correctly"
            );
            
            // Change to second threshold
            $threshold2 = mt_rand(501, 1000) / 1000.0;
            Config::set('face_matching.threshold', $threshold2);
            $retrieved2 = FaceMatchingConfig::getSimilarityThreshold();
            
            $this->assertEquals(
                $threshold2,
                $retrieved2,
                "Second threshold should be retrieved correctly after change"
            );
            
            // Verify the values are different
            $this->assertNotEquals(
                $threshold1,
                $threshold2,
                "Test thresholds should be different to verify change detection"
            );
        }
        
        $this->assertTrue(true, "All {$iterations} configuration changes were immediately reflected");
    }

    /**
     * Property: Default threshold is used when config is not set
     * 
     * When no threshold is configured, getSimilarityThreshold() must return
     * the default value (0.6).
     * 
     * @test
     */
    public function property_default_threshold_is_used_when_config_not_set(): void
    {
        // Test that when config returns null, we still get the default
        // This simulates the config not being set
        Config::set('face_matching', []);
        
        // Get threshold (should fall back to default)
        $threshold = FaceMatchingConfig::getSimilarityThreshold();
        
        // Verify default is returned
        $this->assertEquals(
            FaceMatchingConfig::DEFAULT_THRESHOLD,
            $threshold,
            "When config is not set, default threshold (0.6) should be returned"
        );
        
        // Verify the default constant value
        $this->assertEquals(
            0.6,
            FaceMatchingConfig::DEFAULT_THRESHOLD,
            "DEFAULT_THRESHOLD constant should be 0.6"
        );
    }

    /**
     * Property: Multiple calls return consistent values
     * 
     * Calling getSimilarityThreshold() multiple times without config changes
     * must return the same value (idempotence).
     * 
     * @test
     */
    public function property_multiple_calls_return_consistent_values(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Set a random threshold
            $configuredThreshold = mt_rand(0, 1000) / 1000.0;
            Config::set('face_matching.threshold', $configuredThreshold);
            
            // Call getSimilarityThreshold multiple times
            $call1 = FaceMatchingConfig::getSimilarityThreshold();
            $call2 = FaceMatchingConfig::getSimilarityThreshold();
            $call3 = FaceMatchingConfig::getSimilarityThreshold();
            
            // All calls should return the same value
            $this->assertEquals(
                $call1,
                $call2,
                "First and second calls should return identical values"
            );
            
            $this->assertEquals(
                $call2,
                $call3,
                "Second and third calls should return identical values"
            );
            
            $this->assertEquals(
                $configuredThreshold,
                $call1,
                "All calls should return the configured value"
            );
        }
        
        $this->assertTrue(true, "All {$iterations} consistency checks passed");
    }

    /**
     * Property: Configuration consistency across boundary values
     * 
     * Boundary values (0.0 and 1.0) should be consistently retrieved.
     * 
     * @test
     */
    public function property_configuration_consistency_at_boundaries(): void
    {
        // Test lower boundary (0.0)
        Config::set('face_matching.threshold', 0.0);
        $retrieved = FaceMatchingConfig::getSimilarityThreshold();
        $this->assertEquals(
            0.0,
            $retrieved,
            "Boundary value 0.0 should be consistently retrieved"
        );
        
        // Test upper boundary (1.0)
        Config::set('face_matching.threshold', 1.0);
        $retrieved = FaceMatchingConfig::getSimilarityThreshold();
        $this->assertEquals(
            1.0,
            $retrieved,
            "Boundary value 1.0 should be consistently retrieved"
        );
        
        // Test default (0.6)
        Config::set('face_matching.threshold', 0.6);
        $retrieved = FaceMatchingConfig::getSimilarityThreshold();
        $this->assertEquals(
            0.6,
            $retrieved,
            "Default value 0.6 should be consistently retrieved"
        );
    }

    /**
     * Property: Configuration consistency with environment variable
     * 
     * When FACE_MATCHING_THRESHOLD env var is set, it should be used
     * as the default value.
     * 
     * @test
     */
    public function property_configuration_respects_environment_variable(): void
    {
        // This test verifies the config file structure
        // The actual env var testing would require environment manipulation
        
        // Verify that the config file uses env() with proper default
        $configPath = config_path('face_matching.php');
        $this->assertFileExists($configPath, "Config file should exist");
        
        $configContent = file_get_contents($configPath);
        $this->assertStringContainsString(
            "env('FACE_MATCHING_THRESHOLD'",
            $configContent,
            "Config file should use FACE_MATCHING_THRESHOLD environment variable"
        );
        
        $this->assertStringContainsString(
            "0.6",
            $configContent,
            "Config file should have 0.6 as default fallback"
        );
    }

    /**
     * Property: getChunkSize returns consistent values based on photo count
     * 
     * For the same photo count, getChunkSize() should always return the same value.
     * 
     * @test
     */
    public function property_get_chunk_size_returns_consistent_values(): void
    {
        $testCases = [
            100 => 100,      // Small album: returns photo count
            1000 => 1000,    // Medium album: returns photo count
            5000 => 5000,    // At threshold: returns photo count
            5001 => 500,     // Above threshold: returns chunk size
            10000 => 500,    // Large album: returns chunk size
        ];

        foreach ($testCases as $photoCount => $expectedChunkSize) {
            // Call multiple times to verify consistency
            $call1 = FaceMatchingConfig::getChunkSize($photoCount);
            $call2 = FaceMatchingConfig::getChunkSize($photoCount);
            $call3 = FaceMatchingConfig::getChunkSize($photoCount);
            
            $this->assertEquals(
                $call1,
                $call2,
                "getChunkSize({$photoCount}) should return consistent values"
            );
            
            $this->assertEquals(
                $call2,
                $call3,
                "getChunkSize({$photoCount}) should return consistent values"
            );
            
            $this->assertEquals(
                $expectedChunkSize,
                $call1,
                "getChunkSize({$photoCount}) should return {$expectedChunkSize}"
            );
        }
        
        $this->assertTrue(true, "All chunk size calculations were consistent");
    }
}
