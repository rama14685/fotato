<?php

namespace Tests\Unit\Services\FaceMatching;

use App\Services\FaceMatching\FaceMatchingConfig;
use App\Services\FaceMatching\Exceptions\InvalidThresholdException;
use Tests\TestCase;

/**
 * Property 12: Threshold Validation
 * 
 * **Validates: Requirements 6.3**
 * 
 * This property test verifies that threshold validation correctly rejects
 * all values outside the valid range [0.0, 1.0].
 * 
 * Property: For all threshold values outside [0.0, 1.0], validateThreshold
 * must throw InvalidThresholdException.
 */
class ThresholdValidationPropertyTest extends TestCase
{
    /**
     * Property: Threshold validation rejects values below 0.0
     * 
     * For all values < 0.0, validateThreshold must throw InvalidThresholdException
     * 
     * @test
     */
    public function property_threshold_validation_rejects_negative_values(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random negative threshold
            $invalidThreshold = -mt_rand(1, 10000) / 1000.0;
            
            try {
                FaceMatchingConfig::validateThreshold($invalidThreshold);
                
                // If no exception was thrown, the test fails
                $this->fail(
                    "Expected InvalidThresholdException for threshold {$invalidThreshold}, " .
                    "but no exception was thrown"
                );
            } catch (InvalidThresholdException $e) {
                // Expected behavior - validation correctly rejected the value
                $this->assertStringContainsString(
                    'Threshold must be between 0.0 and 1.0',
                    $e->getMessage(),
                    "Exception message should indicate valid range"
                );
                $this->assertStringContainsString(
                    (string)$invalidThreshold,
                    $e->getMessage(),
                    "Exception message should include the invalid value"
                );
            }
        }
        
        $this->assertTrue(true, "All {$iterations} negative thresholds were correctly rejected");
    }

    /**
     * Property: Threshold validation rejects values above 1.0
     * 
     * For all values > 1.0, validateThreshold must throw InvalidThresholdException
     * 
     * @test
     */
    public function property_threshold_validation_rejects_values_above_one(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random threshold above 1.0
            $invalidThreshold = 1.0 + (mt_rand(1, 10000) / 1000.0);
            
            try {
                FaceMatchingConfig::validateThreshold($invalidThreshold);
                
                // If no exception was thrown, the test fails
                $this->fail(
                    "Expected InvalidThresholdException for threshold {$invalidThreshold}, " .
                    "but no exception was thrown"
                );
            } catch (InvalidThresholdException $e) {
                // Expected behavior - validation correctly rejected the value
                $this->assertStringContainsString(
                    'Threshold must be between 0.0 and 1.0',
                    $e->getMessage(),
                    "Exception message should indicate valid range"
                );
                $this->assertStringContainsString(
                    (string)$invalidThreshold,
                    $e->getMessage(),
                    "Exception message should include the invalid value"
                );
            }
        }
        
        $this->assertTrue(true, "All {$iterations} above-range thresholds were correctly rejected");
    }

    /**
     * Property: Threshold validation accepts all values in [0.0, 1.0]
     * 
     * For all values in [0.0, 1.0], validateThreshold must not throw exception
     * 
     * @test
     */
    public function property_threshold_validation_accepts_valid_range(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random valid threshold in [0.0, 1.0]
            $validThreshold = mt_rand(0, 1000) / 1000.0;
            
            try {
                FaceMatchingConfig::validateThreshold($validThreshold);
                
                // Expected behavior - no exception for valid values
                $this->assertTrue(
                    true,
                    "Threshold {$validThreshold} should be accepted"
                );
            } catch (InvalidThresholdException $e) {
                $this->fail(
                    "Valid threshold {$validThreshold} was incorrectly rejected: " .
                    $e->getMessage()
                );
            }
        }
        
        $this->assertTrue(true, "All {$iterations} valid thresholds were correctly accepted");
    }

    /**
     * Property: Threshold validation handles boundary values correctly
     * 
     * Boundary values 0.0 and 1.0 should be accepted (inclusive range)
     * 
     * @test
     */
    public function property_threshold_validation_accepts_boundary_values(): void
    {
        // Test lower boundary (0.0)
        try {
            FaceMatchingConfig::validateThreshold(0.0);
            $this->assertTrue(true, "Threshold 0.0 should be accepted");
        } catch (InvalidThresholdException $e) {
            $this->fail("Boundary value 0.0 was incorrectly rejected: " . $e->getMessage());
        }

        // Test upper boundary (1.0)
        try {
            FaceMatchingConfig::validateThreshold(1.0);
            $this->assertTrue(true, "Threshold 1.0 should be accepted");
        } catch (InvalidThresholdException $e) {
            $this->fail("Boundary value 1.0 was incorrectly rejected: " . $e->getMessage());
        }
    }

    /**
     * Property: Threshold validation handles extreme invalid values
     * 
     * Very large positive and negative values should be rejected
     * 
     * @test
     */
    public function property_threshold_validation_rejects_extreme_values(): void
    {
        $extremeValues = [
            -1000000.0,
            -100.0,
            -10.0,
            10.0,
            100.0,
            1000000.0,
            PHP_FLOAT_MAX,
            -PHP_FLOAT_MAX,
        ];

        foreach ($extremeValues as $extremeValue) {
            try {
                FaceMatchingConfig::validateThreshold($extremeValue);
                
                $this->fail(
                    "Expected InvalidThresholdException for extreme threshold {$extremeValue}, " .
                    "but no exception was thrown"
                );
            } catch (InvalidThresholdException $e) {
                // Expected behavior
                $this->assertStringContainsString(
                    'Threshold must be between 0.0 and 1.0',
                    $e->getMessage()
                );
            }
        }
        
        $this->assertTrue(true, "All extreme values were correctly rejected");
    }
}
