<?php

namespace App\Services\FaceMatching\Exceptions;

/**
 * Exception thrown when embedding validation fails
 * 
 * Raised when embeddings have invalid dimensions, contain non-numeric values,
 * or fail other validation checks. Provides specific factory methods for
 * different validation failure scenarios.
 */
class InvalidEmbeddingException extends FaceMatchingException
{
    /**
     * Create exception for null embedding
     * 
     * @param string $context Context description (e.g., "customer", "photo")
     * @return self
     */
    public static function nullEmbedding(string $context): self
    {
        // Handle special case for collection
        if (stripos($context, 'collection') !== false) {
            $message = "{$context} cannot be null";
        } else {
            $message = "{$context} embedding cannot be null";
        }
        
        return new self(
            $message,
            context: ['context' => $context]
        );
    }

    /**
     * Create exception for invalid dimensions
     * 
     * @param string $context Context description (e.g., "customer", "photo")
     * @param int $actual Actual dimension count
     * @param int $expected Expected dimension count
     * @return self
     */
    public static function invalidDimensions(
        string $context,
        int $actual,
        int $expected
    ): self {
        $contextLabel = ucfirst($context);
        return new self(
            "{$contextLabel} embedding must have exactly {$expected} dimensions, got {$actual}",
            context: [
                'context' => $context,
                'actual_dimensions' => $actual,
                'expected_dimensions' => $expected,
            ]
        );
    }

    /**
     * Create exception for non-numeric values in embedding
     * 
     * @param string $context Context description (e.g., "customer", "photo")
     * @return self
     */
    public static function nonNumericValue(string $context): self
    {
        return new self(
            'Embedding must contain only numeric values',
            context: ['context' => $context]
        );
    }

    /**
     * Create exception for invalid photo data structure
     * 
     * @return self
     */
    public static function invalidPhotoData(): self
    {
        return new self(
            'Each photo embedding must be an instance of PhotoEmbeddingData',
            context: []
        );
    }

    /**
     * Create exception for invalid photo ID
     * 
     * @return self
     */
    public static function invalidPhotoId(): self
    {
        return new self(
            'Each photo embedding must have a valid photo ID',
            context: []
        );
    }

    /**
     * Create exception for invalid embeddings structure
     * 
     * @return self
     */
    public static function invalidEmbeddingsStructure(): self
    {
        return new self(
            'Photo embeddings must be an array of numeric arrays',
            context: []
        );
    }

    /**
     * Create exception for empty embeddings array
     * 
     * @return self
     */
    public static function emptyEmbeddings(): self
    {
        return new self(
            'Photo must have at least one embedding',
            context: []
        );
    }
}
