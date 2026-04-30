<?php

namespace App\Services\FaceMatching\DTOs;

/**
 * Result of face matching operation
 * 
 * Represents the result of matching a customer face against a photo.
 * 
 * IMMUTABILITY & THREAD SAFETY:
 * - This is a readonly class (PHP 8.2+) - all properties are immutable
 * - Once created, the object cannot be modified
 * - Safe for concurrent usage and sharing between threads
 * - No defensive copying needed when passing between components
 */
readonly class MatchResult
{
    /**
     * Initialize match result
     * 
     * @param int|string $photoId Unique identifier for the photo
     * @param float $similarityScore Cosine similarity score (-1.0 to 1.0)
     * @param bool $matchesThreshold Whether the similarity score meets the threshold
     */
    public function __construct(
        public int|string $photoId,
        public float $similarityScore,
        public bool $matchesThreshold
    ) {}

    /**
     * Create a MatchResult from similarity score and threshold
     * 
     * Factory method for creating a MatchResult by comparing a similarity score
     * against a threshold value. Automatically determines if the match meets
     * the threshold requirement.
     * 
     * @param int|string $photoId Unique identifier for the photo
     * @param float $similarityScore Cosine similarity score (-1.0 to 1.0)
     * @param float $threshold Similarity threshold for matching
     * @return self New MatchResult instance
     */
    public static function create(
        int|string $photoId,
        float $similarityScore,
        float $threshold
    ): self {
        return new self(
            $photoId,
            $similarityScore,
            $similarityScore >= $threshold
        );
    }
}
