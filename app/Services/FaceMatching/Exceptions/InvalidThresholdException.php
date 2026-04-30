<?php

namespace App\Services\FaceMatching\Exceptions;

/**
 * Exception thrown when threshold validation fails
 * 
 * Raised when similarity threshold values are outside the valid range (0.0-1.0)
 * or fail other validation checks.
 */
class InvalidThresholdException extends FaceMatchingException
{
    /**
     * Create exception for threshold out of range
     * 
     * @param float $value The invalid threshold value
     * @return self
     */
    public static function outOfRange(float $value): self
    {
        return new self(
            "Threshold must be between 0.0 and 1.0, got {$value}",
            context: [
                'threshold_value' => $value,
                'min_threshold' => 0.0,
                'max_threshold' => 1.0,
            ]
        );
    }
}
