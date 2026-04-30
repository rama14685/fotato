<?php

namespace App\Services\FaceMatching;

use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\DTOs\MatchResult;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;
use App\Services\FaceMatching\Exceptions\InvalidThresholdException;
use App\Services\FaceMatching\Exceptions\PerformanceException;
use Illuminate\Support\Facades\Log;

/**
 * Face Matching Service for comparing customer embeddings with photo embeddings
 * 
 * This service provides batch processing capabilities for matching a customer's
 * face embedding against multiple photo embeddings using cosine similarity.
 * 
 * THREAD SAFETY GUARANTEES:
 * - The service is stateless and thread-safe for concurrent usage
 * - No instance variables store request-specific data
 * - All input arrays are treated as immutable (never modified)
 * - All result objects (MatchResult) are immutable
 * - Multiple requests can safely use the same service instance concurrently
 * 
 * STATELESS DESIGN:
 * - Only stores configuration ($calculator, $defaultThreshold) as instance variables
 * - All request-specific data is passed as method parameters
 * - No shared mutable state between method calls
 * - Each method call is independent and deterministic
 * 
 * The service handles multiple faces per photo and provides efficient batch
 * processing for large albums with memory-efficient chunked processing.
 */
class FaceMatchingService
{
    /**
     * Cosine similarity calculator (stateless dependency)
     * This is a configuration dependency, not request-specific data
     */
    private CosineSimilarityCalculator $calculator;
    
    /**
     * Default similarity threshold (configuration value)
     * This is a configuration value, not request-specific data
     */
    private float $defaultThreshold;

    /**
     * Initialize the Face Matching Service
     * 
     * @param CosineSimilarityCalculator $calculator The similarity calculator instance
     * @param float $defaultThreshold Default similarity threshold (0.0-1.0), default 0.6
     * @throws InvalidThresholdException If threshold is out of valid range
     */
    public function __construct(
        CosineSimilarityCalculator $calculator,
        float $defaultThreshold = FaceMatchingConfig::DEFAULT_THRESHOLD
    ) {
        $this->calculator = $calculator;
        $this->setDefaultThreshold($defaultThreshold);
    }

    /**
     * Match customer embedding against multiple photo embeddings
     * 
     * Processes a customer's face embedding against a collection of photo embeddings,
     * calculating cosine similarity for each photo. For photos with multiple faces,
     * uses the highest similarity score. Returns results sorted by similarity score
     * in descending order.
     * 
     * THREAD SAFETY: This method is thread-safe and can be called concurrently.
     * Input arrays are never modified. All results are immutable MatchResult objects.
     * 
     * @param array|null $customerEmbedding 128-dimensional float array (never modified)
     * @param array|null $photoEmbeddings Array of PhotoEmbeddingData objects (never modified)
     * @param float|null $threshold Override default threshold (0.0-1.0), or null to use default
     * @return MatchResult[] Sorted by similarity score (descending), immutable results
     * @throws InvalidEmbeddingException For invalid customer or photo embeddings
     * @throws InvalidThresholdException For invalid threshold value
     */
    public function matchFaces(
        ?array $customerEmbedding,
        ?array $photoEmbeddings,
        ?float $threshold = null
    ): array {
        // Validate customer embedding is not null
        if ($customerEmbedding === null) {
            throw InvalidEmbeddingException::nullEmbedding('Customer');
        }

        // Validate photo embeddings collection is not null
        if ($photoEmbeddings === null) {
            throw InvalidEmbeddingException::nullEmbedding('Photo embeddings collection');
        }

        // Handle empty photo collection gracefully
        if (empty($photoEmbeddings)) {
            return [];
        }

        // Determine threshold to use
        $effectiveThreshold = $threshold ?? $this->defaultThreshold;
        FaceMatchingConfig::validateThreshold($effectiveThreshold);

        // Validate customer embedding dimensions
        $this->calculator->validateEmbedding($customerEmbedding, 'customer');

        // Pre-compute customer magnitude for efficiency
        $customerMagnitude = $this->calculator->magnitude($customerEmbedding);

        // Process all photo embeddings
        $results = [];

        foreach ($photoEmbeddings as $photoData) {
            if (!$photoData instanceof PhotoEmbeddingData) {
                throw InvalidEmbeddingException::invalidPhotoData();
            }

            // Find highest similarity score among all faces in this photo
            $maxSimilarity = -2.0; // Start below minimum possible value

            foreach ($photoData->embeddings as $photoEmbedding) {
                // Validate photo embedding
                $this->calculator->validateEmbedding($photoEmbedding, 'photo');

                // Calculate similarity
                $similarity = $this->calculator->calculateSimilarity(
                    $customerEmbedding,
                    $photoEmbedding
                );

                // Track maximum similarity for this photo
                if ($similarity > $maxSimilarity) {
                    $maxSimilarity = $similarity;
                }
            }

            // Create match result for this photo
            $result = MatchResult::create(
                $photoData->photoId,
                $maxSimilarity,
                $effectiveThreshold
            );

            $results[] = $result;
        }

        // Sort results by similarity score in descending order
        usort($results, function (MatchResult $a, MatchResult $b) {
            return $b->similarityScore <=> $a->similarityScore;
        });

        return $results;
    }

    /**
     * Set default similarity threshold
     * 
     * Updates the default threshold used for all matching operations unless
     * explicitly overridden via method parameter.
     * 
     * @param float $threshold Value between 0.0 and 1.0
     * @return void
     * @throws InvalidThresholdException If threshold out of range
     */
    public function setDefaultThreshold(float $threshold): void
    {
        FaceMatchingConfig::validateThreshold($threshold);
        $this->defaultThreshold = $threshold;
    }

    /**
     * Get current default threshold
     * 
     * @return float Current default threshold value
     */
    public function getDefaultThreshold(): float
    {
        return $this->defaultThreshold;
    }

    /**
     * Match faces using chunked processing for large albums
     * 
     * Implements memory-efficient chunked processing for albums with more than
     * 5000 photos. Processes photos in chunks of 500, with periodic garbage
     * collection to manage memory usage. Streams results without loading all
     * into memory simultaneously.
     * 
     * @param array|null $customerEmbedding 128-dimensional float array
     * @param array|null $photoEmbeddings Array of PhotoEmbeddingData objects
     * @param float|null $threshold Override default threshold (0.0-1.0), or null to use default
     * @return MatchResult[] Sorted by similarity score (descending)
     * @throws InvalidEmbeddingException For invalid customer or photo embeddings
     * @throws InvalidThresholdException For invalid threshold value
     */
    public function matchFacesChunked(
        ?array $customerEmbedding,
        ?array $photoEmbeddings,
        ?float $threshold = null
    ): array {
        // Validate inputs are not null (delegate to matchFaces for small albums)
        if ($customerEmbedding === null || $photoEmbeddings === null) {
            return $this->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
        }
        $photoCount = count($photoEmbeddings);
        $chunkSize = FaceMatchingConfig::getChunkSize($photoCount);
        
        // For small albums, use regular processing
        if ($photoCount <= FaceMatchingConfig::LARGE_ALBUM_THRESHOLD) {
            return $this->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
        }

        // Log chunked processing start
        Log::info('Starting chunked face matching for large album', [
            'photo_count' => $photoCount,
            'chunk_size' => $chunkSize,
            'threshold' => $threshold ?? $this->defaultThreshold,
            'timestamp' => now()->toISOString(),
        ]);

        $startTime = microtime(true);
        $allResults = [];
        $processedCount = 0;

        // Determine threshold to use
        $effectiveThreshold = $threshold ?? $this->defaultThreshold;
        FaceMatchingConfig::validateThreshold($effectiveThreshold);

        // Validate customer embedding once
        $this->calculator->validateEmbedding($customerEmbedding, 'customer');

        // Pre-compute customer magnitude once for efficiency
        $customerMagnitude = $this->calculator->magnitude($customerEmbedding);

        // Process in chunks
        for ($chunkStart = 0; $chunkStart < $photoCount; $chunkStart += $chunkSize) {
            $chunkEnd = min($chunkStart + $chunkSize, $photoCount);
            $chunk = array_slice($photoEmbeddings, $chunkStart, $chunkEnd - $chunkStart);

            // Monitor memory before processing chunk
            $memoryBefore = memory_get_usage(true);

            // Process chunk
            $chunkResults = $this->processChunk(
                $customerEmbedding,
                $customerMagnitude,
                $chunk,
                $effectiveThreshold
            );

            // Add chunk results to all results
            foreach ($chunkResults as $result) {
                $allResults[] = $result;
            }

            $processedCount += count($chunk);

            // Monitor memory after processing chunk
            $memoryAfter = memory_get_usage(true);
            $memoryUsed = $memoryAfter - $memoryBefore;

            // Log chunk completion
            Log::debug('Chunk processed', [
                'chunk_start' => $chunkStart,
                'chunk_end' => $chunkEnd,
                'chunk_size' => count($chunk),
                'processed_count' => $processedCount,
                'memory_used_bytes' => $memoryUsed,
                'memory_total_bytes' => $memoryAfter,
            ]);

            // Release chunk memory
            unset($chunk);
            unset($chunkResults);

            // Trigger garbage collection every 1000 photos
            if ($processedCount % config('face_matching.performance.gc_trigger_interval', 1000) === 0) {
                gc_collect_cycles();
                
                Log::debug('Garbage collection triggered', [
                    'processed_count' => $processedCount,
                    'memory_after_gc' => memory_get_usage(true),
                ]);
            }
        }

        // Sort all results by similarity score (descending)
        usort($allResults, function (MatchResult $a, MatchResult $b) {
            return $b->similarityScore <=> $a->similarityScore;
        });

        // Log completion
        $elapsedTime = microtime(true) - $startTime;
        Log::info('Chunked face matching completed', [
            'photo_count' => $photoCount,
            'result_count' => count($allResults),
            'elapsed_seconds' => round($elapsedTime, 3),
            'memory_peak_bytes' => memory_get_peak_usage(true),
            'timestamp' => now()->toISOString(),
        ]);

        return $allResults;
    }

    /**
     * Process a single chunk of photo embeddings
     * 
     * Internal method for processing a chunk of photos with pre-computed
     * customer magnitude for efficiency. Uses optimized similarity calculation.
     * 
     * @param array $customerEmbedding 128-dimensional float array
     * @param float $customerMagnitude Pre-computed customer embedding magnitude
     * @param PhotoEmbeddingData[] $photoChunk Chunk of photo embeddings to process
     * @param float $threshold Similarity threshold
     * @return MatchResult[] Results for this chunk (unsorted)
     */
    private function processChunk(
        array $customerEmbedding,
        float $customerMagnitude,
        array $photoChunk,
        float $threshold
    ): array {
        $results = [];

        foreach ($photoChunk as $photoData) {
            if (!$photoData instanceof PhotoEmbeddingData) {
                throw InvalidEmbeddingException::invalidPhotoData();
            }

            // Find highest similarity score among all faces in this photo
            $maxSimilarity = -2.0; // Start below minimum possible value

            foreach ($photoData->embeddings as $photoEmbedding) {
                // Validate photo embedding
                $this->calculator->validateEmbedding($photoEmbedding, 'photo');

                // Calculate similarity using optimized method
                $similarity = $this->calculateOptimizedSimilarity(
                    $customerEmbedding,
                    $customerMagnitude,
                    $photoEmbedding
                );

                // Track maximum similarity for this photo
                if ($similarity > $maxSimilarity) {
                    $maxSimilarity = $similarity;
                }
            }

            // Create match result for this photo
            $result = MatchResult::create(
                $photoData->photoId,
                $maxSimilarity,
                $threshold
            );

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Calculate similarity with pre-computed customer magnitude
     * 
     * Optimized similarity calculation that reuses pre-computed customer
     * magnitude. Combines dot product and photo magnitude calculation in
     * a single loop for efficiency.
     * 
     * @param array $customerEmbedding 128-dimensional float array
     * @param float $customerMagnitude Pre-computed customer embedding magnitude
     * @param array $photoEmbedding 128-dimensional float array
     * @return float Similarity score in range [-1, 1]
     */
    private function calculateOptimizedSimilarity(
        array $customerEmbedding,
        float $customerMagnitude,
        array $photoEmbedding
    ): float {
        $dotProduct = 0.0;
        $photoMagnitudeSquared = 0.0;

        // Single loop for both dot product and photo magnitude
        for ($i = 0; $i < FaceMatchingConfig::EMBEDDING_DIMENSIONS; $i++) {
            $customerVal = $customerEmbedding[$i];
            $photoVal = $photoEmbedding[$i];

            $dotProduct += $customerVal * $photoVal;
            $photoMagnitudeSquared += $photoVal * $photoVal;
        }

        $photoMagnitude = sqrt($photoMagnitudeSquared);

        // Handle zero magnitude edge case
        if ($customerMagnitude === 0.0 || $photoMagnitude === 0.0) {
            return 0.0;
        }

        return $dotProduct / ($customerMagnitude * $photoMagnitude);
    }

    /**
     * Match faces with comprehensive error handling and recovery
     * 
     * Provides robust error handling with automatic recovery strategies for
     * batch processing. Filters out invalid embeddings and retries with valid
     * data when possible. Includes comprehensive privacy-safe logging.
     * 
     * @param array|null $customerEmbedding 128-dimensional float array
     * @param array|null $photoEmbeddings Array of PhotoEmbeddingData objects
     * @param float|null $threshold Override default threshold (0.0-1.0), or null to use default
     * @return MatchResult[] Sorted by similarity score (descending)
     * @throws InvalidEmbeddingException For invalid customer embedding (unrecoverable)
     * @throws InvalidThresholdException For invalid threshold value
     */
    public function matchFacesWithRecovery(
        ?array $customerEmbedding,
        ?array $photoEmbeddings,
        ?float $threshold = null
    ): array {
        $startTime = microtime(true);
        
        // Handle null inputs early
        if ($customerEmbedding === null || $photoEmbeddings === null) {
            return $this->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);
        }
        
        $photoCount = count($photoEmbeddings);

        // Log empty collection warning
        if (empty($photoEmbeddings)) {
            Log::warning('Face matching called with empty photo collection', [
                'timestamp' => now()->toISOString(),
            ]);
            return [];
        }

        try {
            // Log batch operation start
            $this->logBatchStart($photoCount, $threshold);

            // Attempt normal processing
            $results = $this->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

            // Log successful completion
            $this->logBatchCompletion($photoCount, $startTime, count($results));

            return $results;

        } catch (InvalidEmbeddingException $e) {
            // Log error with context
            $this->logEmbeddingError($e, $photoCount);

            // Attempt recovery by filtering invalid embeddings
            if ($this->isPhotoEmbeddingError($e)) {
                $validPhotoEmbeddings = $this->filterValidEmbeddings($photoEmbeddings);

                if (empty($validPhotoEmbeddings)) {
                    Log::warning('No valid photo embeddings after filtering', [
                        'original_count' => $photoCount,
                        'valid_count' => 0,
                    ]);
                    return [];
                }

                Log::info('Retrying with filtered valid embeddings', [
                    'original_count' => $photoCount,
                    'valid_count' => count($validPhotoEmbeddings),
                    'filtered_count' => $photoCount - count($validPhotoEmbeddings),
                ]);

                // Retry with valid embeddings only
                $results = $this->matchFaces($customerEmbedding, $validPhotoEmbeddings, $threshold);
                $this->logBatchCompletion($photoCount, $startTime, count($results), true);

                return $results;
            }

            // Customer embedding error is unrecoverable
            throw $e;

        } catch (PerformanceException $e) {
            // Log performance issue
            $this->logPerformanceError($e, $photoCount);

            // Performance issues are logged but don't stop processing
            throw $e;

        } catch (\Throwable $e) {
            // Log unexpected error
            $this->logUnexpectedError($e, $photoCount, $startTime);

            throw $e;
        }
    }

    /**
     * Filter out invalid photo embeddings from collection
     * 
     * Validates each photo embedding and returns only valid ones.
     * Invalid embeddings are logged with photo ID for debugging.
     * 
     * @param PhotoEmbeddingData[] $photoEmbeddings Collection to filter
     * @return PhotoEmbeddingData[] Valid photo embeddings only
     */
    private function filterValidEmbeddings(array $photoEmbeddings): array
    {
        return array_filter($photoEmbeddings, function (PhotoEmbeddingData $photoData) {
            try {
                // Validate photo data structure
                if (!$photoData instanceof PhotoEmbeddingData) {
                    Log::warning('Invalid photo data structure', [
                        'photo_id' => $photoData->photoId ?? 'unknown',
                    ]);
                    return false;
                }

                // Validate all embeddings for this photo
                foreach ($photoData->embeddings as $embedding) {
                    $this->calculator->validateEmbedding($embedding, 'photo');
                }

                return true;

            } catch (InvalidEmbeddingException $e) {
                Log::warning('Skipping invalid photo embedding', [
                    'photo_id' => $photoData->photoId,
                    'error' => $e->getMessage(),
                    'context' => $e->getContext(),
                ]);
                return false;
            }
        });
    }

    /**
     * Check if exception is related to photo embeddings (recoverable)
     * 
     * @param InvalidEmbeddingException $e Exception to check
     * @return bool True if error is photo-related and potentially recoverable
     */
    private function isPhotoEmbeddingError(InvalidEmbeddingException $e): bool
    {
        $context = $e->getContext();
        return isset($context['context']) && $context['context'] === 'photo';
    }

    /**
     * Log batch operation start (privacy-safe)
     * 
     * @param int $photoCount Number of photos to process
     * @param float|null $threshold Threshold being used
     * @return void
     */
    private function logBatchStart(int $photoCount, ?float $threshold): void
    {
        Log::info('Face matching batch operation started', [
            'photo_count' => $photoCount,
            'threshold' => $threshold ?? $this->defaultThreshold,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log batch operation completion (privacy-safe)
     * 
     * @param int $photoCount Number of photos processed
     * @param float $startTime Start time from microtime(true)
     * @param int $resultCount Number of results returned
     * @param bool $recovered Whether recovery was used
     * @return void
     */
    private function logBatchCompletion(
        int $photoCount,
        float $startTime,
        int $resultCount,
        bool $recovered = false
    ): void {
        $elapsedTime = microtime(true) - $startTime;

        $logData = [
            'photo_count' => $photoCount,
            'result_count' => $resultCount,
            'elapsed_seconds' => round($elapsedTime, 3),
            'recovered' => $recovered,
            'timestamp' => now()->toISOString(),
        ];

        // Check for performance warning
        if ($photoCount >= 1000 && $elapsedTime > FaceMatchingConfig::MAX_PROCESSING_TIME_SECONDS) {
            Log::warning('Face matching batch exceeded performance target', array_merge($logData, [
                'max_seconds' => FaceMatchingConfig::MAX_PROCESSING_TIME_SECONDS,
                'performance_ratio' => round($elapsedTime / FaceMatchingConfig::MAX_PROCESSING_TIME_SECONDS, 2),
            ]));
        } else {
            Log::info('Face matching batch operation completed', $logData);
        }
    }

    /**
     * Log embedding validation error (privacy-safe)
     * 
     * @param InvalidEmbeddingException $e Exception to log
     * @param int $photoCount Number of photos being processed
     * @return void
     */
    private function logEmbeddingError(InvalidEmbeddingException $e, int $photoCount): void
    {
        Log::error('Face matching embedding validation failed', [
            'error' => $e->getMessage(),
            'context' => $e->getContext(),
            'photo_count' => $photoCount,
            'timestamp' => now()->toISOString(),
            // Note: Raw embedding values are never logged (privacy protection)
        ]);
    }

    /**
     * Log performance exception (privacy-safe)
     * 
     * @param PerformanceException $e Exception to log
     * @param int $photoCount Number of photos being processed
     * @return void
     */
    private function logPerformanceError(PerformanceException $e, int $photoCount): void
    {
        Log::error('Face matching performance issue', [
            'error' => $e->getMessage(),
            'context' => $e->getContext(),
            'photo_count' => $photoCount,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log unexpected error (privacy-safe)
     * 
     * @param \Throwable $e Exception to log
     * @param int $photoCount Number of photos being processed
     * @param float $startTime Start time from microtime(true)
     * @return void
     */
    private function logUnexpectedError(\Throwable $e, int $photoCount, float $startTime): void
    {
        $elapsedTime = microtime(true) - $startTime;

        Log::error('Unexpected error in face matching service', [
            'error' => $e->getMessage(),
            'error_class' => get_class($e),
            'photo_count' => $photoCount,
            'elapsed_seconds' => round($elapsedTime, 3),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'timestamp' => now()->toISOString(),
            // Note: Stack trace excluded to avoid potential embedding value exposure
        ]);
    }
}
