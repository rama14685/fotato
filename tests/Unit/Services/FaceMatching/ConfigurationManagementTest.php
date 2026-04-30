<?php

namespace Tests\Unit\Services\FaceMatching;

use App\Services\FaceMatching\FaceMatchingConfig;
use App\Services\FaceMatching\Exceptions\InvalidThresholdException;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Unit tests for FaceMatchingConfig configuration management
 * 
 * Tests threshold validation, configuration file integration,
 * and environment variable override functionality.
 * 
 * Requirements: 6.3, 6.4, 6.5
 */
class ConfigurationManagementTest extends TestCase
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
     * Test: validateThreshold accepts valid threshold values
     * 
     * Requirement 6.3: Validate that threshold values are between 0.0 and 1.0
     * 
     * @test
     */
    public function test_validate_threshold_accepts_valid_values(): void
    {
        $validThresholds = [0.0, 0.1, 0.5, 0.6, 0.9, 1.0];

        foreach ($validThresholds as $threshold) {
            try {
                FaceMatchingConfig::validateThreshold($threshold);
                $this->assertTrue(true, "Threshold {$threshold} should be accepted");
            } catch (InvalidThresholdException $e) {
                $this->fail("Valid threshold {$threshold} was rejected: " . $e->getMessage());
            }
        }
    }

    /**
     * Test: validateThreshold rejects threshold below 0.0
     * 
     * Requirement 6.4: Throw InvalidArgumentException for invalid thresholds
     * 
     * @test
     */
    public function test_validate_threshold_rejects_negative_values(): void
    {
        $this->expectException(InvalidThresholdException::class);
        $this->expectExceptionMessage('Threshold must be between 0.0 and 1.0, got -0.1');

        FaceMatchingConfig::validateThreshold(-0.1);
    }

    /**
     * Test: validateThreshold rejects threshold above 1.0
     * 
     * Requirement 6.4: Throw InvalidArgumentException for invalid thresholds
     * 
     * @test
     */
    public function test_validate_threshold_rejects_values_above_one(): void
    {
        $this->expectException(InvalidThresholdException::class);
        $this->expectExceptionMessage('Threshold must be between 0.0 and 1.0, got 1.5');

        FaceMatchingConfig::validateThreshold(1.5);
    }

    /**
     * Test: validateThreshold error message includes actual value
     * 
     * Requirement 6.4: Error message should include the invalid value
     * 
     * @test
     */
    public function test_validate_threshold_error_message_includes_value(): void
    {
        $invalidValue = 2.5;

        try {
            FaceMatchingConfig::validateThreshold($invalidValue);
            $this->fail("Expected InvalidThresholdException to be thrown");
        } catch (InvalidThresholdException $e) {
            $this->assertStringContainsString(
                (string)$invalidValue,
                $e->getMessage(),
                "Error message should include the invalid value"
            );
            $this->assertStringContainsString(
                'Threshold must be between 0.0 and 1.0',
                $e->getMessage(),
                "Error message should indicate valid range"
            );
        }
    }

    /**
     * Test: getSimilarityThreshold returns configured value
     * 
     * Requirement 6.5: Configuration file integration
     * 
     * @test
     */
    public function test_get_similarity_threshold_returns_configured_value(): void
    {
        // Set a specific threshold in config
        Config::set('face_matching.threshold', 0.75);

        $threshold = FaceMatchingConfig::getSimilarityThreshold();

        $this->assertEquals(0.75, $threshold, "Should return configured threshold value");
    }

    /**
     * Test: getSimilarityThreshold returns default when not configured
     * 
     * Requirement 6.5: Default value fallback
     * 
     * @test
     */
    public function test_get_similarity_threshold_returns_default_when_not_configured(): void
    {
        // Set config to empty array to simulate threshold not being set
        Config::set('face_matching', []);

        $threshold = FaceMatchingConfig::getSimilarityThreshold();

        $this->assertEquals(
            FaceMatchingConfig::DEFAULT_THRESHOLD,
            $threshold,
            "Should return default threshold when not configured"
        );
        $this->assertEquals(0.6, $threshold, "Default threshold should be 0.6");
    }

    /**
     * Test: Configuration file exists and has correct structure
     * 
     * Requirement 6.5: Configuration file integration
     * 
     * @test
     */
    public function test_configuration_file_exists_and_has_correct_structure(): void
    {
        $configPath = config_path('face_matching.php');
        
        $this->assertFileExists($configPath, "Configuration file should exist");

        $config = require $configPath;

        $this->assertIsArray($config, "Config file should return an array");
        $this->assertArrayHasKey('threshold', $config, "Config should have 'threshold' key");
        $this->assertArrayHasKey('performance', $config, "Config should have 'performance' section");
        $this->assertArrayHasKey('validation', $config, "Config should have 'validation' section");
        $this->assertArrayHasKey('logging', $config, "Config should have 'logging' section");
    }

    /**
     * Test: Configuration file supports environment variable override
     * 
     * Requirement 6.5: Environment variable support
     * 
     * @test
     */
    public function test_configuration_file_supports_environment_variable(): void
    {
        $configPath = config_path('face_matching.php');
        $configContent = file_get_contents($configPath);

        $this->assertStringContainsString(
            "env('FACE_MATCHING_THRESHOLD'",
            $configContent,
            "Config file should use FACE_MATCHING_THRESHOLD environment variable"
        );
    }

    /**
     * Test: Configuration constants are defined correctly
     * 
     * Requirement 6.3: Configuration constants
     * 
     * @test
     */
    public function test_configuration_constants_are_defined(): void
    {
        $this->assertEquals(0.6, FaceMatchingConfig::DEFAULT_THRESHOLD);
        $this->assertEquals(128, FaceMatchingConfig::EMBEDDING_DIMENSIONS);
        $this->assertEquals(10, FaceMatchingConfig::MAX_PROCESSING_TIME_SECONDS);
        $this->assertEquals(500, FaceMatchingConfig::CHUNK_SIZE_LARGE_ALBUMS);
        $this->assertEquals(5000, FaceMatchingConfig::LARGE_ALBUM_THRESHOLD);
    }

    /**
     * Test: getChunkSize returns photo count for small albums
     * 
     * Requirement 6.6: Performance optimization configuration
     * 
     * @test
     */
    public function test_get_chunk_size_returns_photo_count_for_small_albums(): void
    {
        $smallAlbumSizes = [100, 500, 1000, 4999, 5000];

        foreach ($smallAlbumSizes as $photoCount) {
            $chunkSize = FaceMatchingConfig::getChunkSize($photoCount);
            
            $this->assertEquals(
                $photoCount,
                $chunkSize,
                "For albums with {$photoCount} photos, chunk size should equal photo count"
            );
        }
    }

    /**
     * Test: getChunkSize returns configured chunk size for large albums
     * 
     * Requirement 6.6: Performance optimization configuration
     * 
     * @test
     */
    public function test_get_chunk_size_returns_chunk_size_for_large_albums(): void
    {
        $largeAlbumSizes = [5001, 6000, 10000, 50000];

        foreach ($largeAlbumSizes as $photoCount) {
            $chunkSize = FaceMatchingConfig::getChunkSize($photoCount);
            
            $this->assertEquals(
                FaceMatchingConfig::CHUNK_SIZE_LARGE_ALBUMS,
                $chunkSize,
                "For albums with {$photoCount} photos, chunk size should be " .
                FaceMatchingConfig::CHUNK_SIZE_LARGE_ALBUMS
            );
        }
    }

    /**
     * Test: getChunkSize handles edge case at threshold boundary
     * 
     * Requirement 6.6: Performance optimization configuration
     * 
     * @test
     */
    public function test_get_chunk_size_handles_threshold_boundary(): void
    {
        // At threshold (5000) - should return photo count
        $chunkSize = FaceMatchingConfig::getChunkSize(5000);
        $this->assertEquals(5000, $chunkSize, "At threshold, should return photo count");

        // Just above threshold (5001) - should return chunk size
        $chunkSize = FaceMatchingConfig::getChunkSize(5001);
        $this->assertEquals(
            FaceMatchingConfig::CHUNK_SIZE_LARGE_ALBUMS,
            $chunkSize,
            "Above threshold, should return chunk size"
        );
    }

    /**
     * Test: Configuration performance section has all required keys
     * 
     * Requirement 6.6: Performance configuration
     * 
     * @test
     */
    public function test_configuration_performance_section_has_required_keys(): void
    {
        $performance = config('face_matching.performance');

        $this->assertIsArray($performance, "Performance config should be an array");
        $this->assertArrayHasKey('max_processing_time_seconds', $performance);
        $this->assertArrayHasKey('chunk_size_large_albums', $performance);
        $this->assertArrayHasKey('large_album_threshold', $performance);
        $this->assertArrayHasKey('memory_limit_mb', $performance);
        $this->assertArrayHasKey('gc_trigger_interval', $performance);
    }

    /**
     * Test: Configuration validation section has all required keys
     * 
     * Requirement 6.3: Validation configuration
     * 
     * @test
     */
    public function test_configuration_validation_section_has_required_keys(): void
    {
        $validation = config('face_matching.validation');

        $this->assertIsArray($validation, "Validation config should be an array");
        $this->assertArrayHasKey('embedding_dimensions', $validation);
        $this->assertArrayHasKey('allow_zero_magnitude', $validation);
        $this->assertArrayHasKey('strict_numeric_validation', $validation);
    }

    /**
     * Test: Configuration logging section has all required keys
     * 
     * Requirement 6.5: Logging configuration
     * 
     * @test
     */
    public function test_configuration_logging_section_has_required_keys(): void
    {
        $logging = config('face_matching.logging');

        $this->assertIsArray($logging, "Logging config should be an array");
        $this->assertArrayHasKey('log_performance_warnings', $logging);
        $this->assertArrayHasKey('log_zero_magnitude_warnings', $logging);
        $this->assertArrayHasKey('log_threshold_changes', $logging);
        $this->assertArrayHasKey('exclude_embedding_values', $logging);
    }

    /**
     * Test: Privacy protection is enabled by default
     * 
     * Requirement 6.5: Privacy protection in logging
     * 
     * @test
     */
    public function test_privacy_protection_is_enabled_by_default(): void
    {
        $excludeEmbeddings = config('face_matching.logging.exclude_embedding_values');

        $this->assertTrue(
            $excludeEmbeddings,
            "Privacy protection should be enabled by default (exclude_embedding_values = true)"
        );
    }

    /**
     * Test: validateThreshold handles extreme values
     * 
     * Requirement 6.4: Validation for extreme values
     * 
     * @test
     */
    public function test_validate_threshold_handles_extreme_values(): void
    {
        $extremeValues = [
            -1000000.0,
            -100.0,
            100.0,
            1000000.0,
        ];

        foreach ($extremeValues as $value) {
            try {
                FaceMatchingConfig::validateThreshold($value);
                $this->fail("Expected InvalidThresholdException for extreme value {$value}");
            } catch (InvalidThresholdException $e) {
                $this->assertStringContainsString(
                    'Threshold must be between 0.0 and 1.0',
                    $e->getMessage(),
                    "Error message should indicate valid range for extreme value {$value}"
                );
            }
        }
    }
}
