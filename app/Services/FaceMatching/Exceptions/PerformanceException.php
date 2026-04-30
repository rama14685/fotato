<?php

namespace App\Services\FaceMatching\Exceptions;

/**
 * Exception thrown for performance-related issues
 * 
 * Raised when processing exceeds time limits, memory constraints, or other
 * performance thresholds. Used for monitoring and alerting on performance issues.
 */
class PerformanceException extends FaceMatchingException
{
    /**
     * Create exception for processing timeout
     * 
     * @param int $photoCount Number of photos being processed
     * @param float $elapsedSeconds Elapsed processing time in seconds
     * @param float $maxSeconds Maximum allowed processing time
     * @return self
     */
    public static function processingTimeout(
        int $photoCount,
        float $elapsedSeconds,
        float $maxSeconds
    ): self {
        return new self(
            "Processing {$photoCount} photos exceeded {$maxSeconds} second limit ({$elapsedSeconds}s elapsed)",
            context: [
                'photo_count' => $photoCount,
                'elapsed_seconds' => $elapsedSeconds,
                'max_seconds' => $maxSeconds,
            ]
        );
    }

    /**
     * Create exception for memory limit exceeded
     * 
     * @param int $photoCount Number of photos being processed
     * @param int $memoryUsedBytes Memory used in bytes
     * @param int $memoryLimitBytes Memory limit in bytes
     * @return self
     */
    public static function memoryLimitExceeded(
        int $photoCount,
        int $memoryUsedBytes,
        int $memoryLimitBytes
    ): self {
        return new self(
            "Processing {$photoCount} photos exceeded memory limit ({$memoryUsedBytes} bytes used, {$memoryLimitBytes} bytes available)",
            context: [
                'photo_count' => $photoCount,
                'memory_used_bytes' => $memoryUsedBytes,
                'memory_limit_bytes' => $memoryLimitBytes,
            ]
        );
    }
}
