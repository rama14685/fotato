<?php

namespace App\Services\FaceMatching;

use App\Services\FaceMatching\Exceptions\InvalidThresholdException;

/**
 * Configuration manager for face matching parameters
 * 
 * Provides centralized configuration management for the Face Matching Service,
 * including threshold validation, embedding dimensions, and performance parameters.
 */
class FaceMatchingConfig
{
    /**
     * Default similarity threshold for face matching
     */
    public const DEFAULT_THRESHOLD = 0.6;

    /**
     * Required number of dimensions for face embeddings
     */
    public const EMBEDDING_DIMENSIONS = 128;

    /**
     * Maximum processing time for batch operations (seconds)
     */
    public const MAX_PROCESSING_TIME_SECONDS = 10;

    /**
     * Chunk size for processing large albums
     */
    public const CHUNK_SIZE_LARGE_ALBUMS = 500;

    /**
     * Threshold for considering an album "large" (triggers chunked processing)
     */
    public const LARGE_ALBUM_THRESHOLD = 5000;

    /**
     * Get similarity threshold from config with fallback to default
     * 
     * Retrieves the configured similarity threshold from Laravel config,
     * with fallback to the default value if not configured.
     * 
     * @return float Similarity threshold value (0.0-1.0)
     */
    public static function getSimilarityThreshold(): float
    {
        return config('face_matching.threshold', self::DEFAULT_THRESHOLD);
    }

    /**
     * Validate threshold value is in valid range
     * 
     * Ensures threshold is between 0.0 and 1.0 (inclusive).
     * Throws InvalidThresholdException if validation fails.
     * 
     * @param float $threshold Threshold value to validate
     * @return void
     * @throws InvalidThresholdException If threshold is out of valid range
     */
    public static function validateThreshold(float $threshold): void
    {
        if ($threshold < 0.0 || $threshold > 1.0) {
            throw InvalidThresholdException::outOfRange($threshold);
        }
    }

    /**
     * Get chunk size for batch processing based on album size
     * 
     * Returns appropriate chunk size for processing large albums efficiently.
     * For albums with more than LARGE_ALBUM_THRESHOLD photos, uses chunked
     * processing to optimize memory usage.
     * 
     * @param int $photoCount Total number of photos to process
     * @return int Recommended chunk size for batch processing
     */
    public static function getChunkSize(int $photoCount): int
    {
        if ($photoCount > self::LARGE_ALBUM_THRESHOLD) {
            return self::CHUNK_SIZE_LARGE_ALBUMS;
        }

        return $photoCount;
    }
}
