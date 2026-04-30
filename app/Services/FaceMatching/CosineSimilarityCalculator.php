<?php

namespace App\Services\FaceMatching;

use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;

/**
 * Pure mathematical calculator for cosine similarity between embedding vectors
 * 
 * This class provides core mathematical functions for computing cosine similarity
 * between 128-dimensional face embedding vectors.
 * 
 * THREAD SAFETY GUARANTEES:
 * - Completely stateless - no instance variables
 * - All methods are pure functions (same inputs always produce same outputs)
 * - Input arrays are never modified
 * - Safe for concurrent usage by multiple threads/requests
 * 
 * Formula: similarity = dot_product(A, B) / (magnitude(A) * magnitude(B))
 * 
 * NOTE: This class is a pure mathematical calculator and does not perform logging.
 * Logging of zero magnitude warnings should be handled by the calling service layer.
 */
class CosineSimilarityCalculator
{
    /**
     * Calculate cosine similarity between two embedding vectors
     * 
     * Computes the cosine similarity using the formula:
     * similarity = dot_product(A, B) / (magnitude(A) * magnitude(B))
     * 
     * For zero magnitude vectors, returns 0.0 to handle edge cases gracefully.
     * 
     * THREAD SAFETY: Pure function - input arrays are never modified.
     * 
     * @param array $embeddingA 128-dimensional float array (never modified)
     * @param array $embeddingB 128-dimensional float array (never modified)
     * @return float Similarity score in range [-1, 1]
     * @throws InvalidEmbeddingException For dimension mismatch or invalid inputs
     */
    public function calculateSimilarity(array $embeddingA, array $embeddingB): float
    {
        // Validate both embeddings have correct dimensions
        $this->validateEmbedding($embeddingA, 'customer');
        $this->validateEmbedding($embeddingB, 'photo');

        // Calculate magnitudes
        $magnitudeA = $this->magnitude($embeddingA);
        $magnitudeB = $this->magnitude($embeddingB);

        // Handle zero magnitude vectors (edge case)
        // Returns 0.0 for undefined cosine similarity when either vector has zero magnitude
        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0;
        }

        // Calculate dot product and divide by product of magnitudes
        $dotProd = $this->dotProduct($embeddingA, $embeddingB);
        return $dotProd / ($magnitudeA * $magnitudeB);
    }

    /**
     * Calculate dot product of two vectors
     * 
     * Computes the sum of element-wise multiplication of corresponding elements.
     * 
     * THREAD SAFETY: Pure function - input arrays are never modified.
     * 
     * @param array $vectorA Numeric array (never modified)
     * @param array $vectorB Numeric array (same length as A, never modified)
     * @return float Sum of element-wise multiplication
     */
    public function dotProduct(array $vectorA, array $vectorB): float
    {
        $sum = 0.0;
        $length = count($vectorA);

        for ($i = 0; $i < $length; $i++) {
            $sum += $vectorA[$i] * $vectorB[$i];
        }

        return $sum;
    }

    /**
     * Calculate L2 norm (Euclidean magnitude) of a vector
     * 
     * Computes the square root of the sum of squared elements.
     * 
     * THREAD SAFETY: Pure function - input array is never modified.
     * 
     * @param array $vector Numeric array (never modified)
     * @return float Square root of sum of squared elements
     */
    public function magnitude(array $vector): float
    {
        $sumOfSquares = 0.0;

        foreach ($vector as $value) {
            $sumOfSquares += $value * $value;
        }

        return sqrt($sumOfSquares);
    }

    /**
     * Validate that embedding has exactly 128 dimensions and all numeric values
     * 
     * Ensures embeddings meet the required specifications before processing.
     * Throws InvalidEmbeddingException with detailed context if validation fails.
     * 
     * @param array $embedding Embedding to validate
     * @param string $context Context for error messages (e.g., "customer", "photo")
     * @return void
     * @throws InvalidEmbeddingException For invalid embeddings
     */
    public function validateEmbedding(array $embedding, string $context): void
    {
        $dimensions = count($embedding);

        if ($dimensions !== FaceMatchingConfig::EMBEDDING_DIMENSIONS) {
            throw InvalidEmbeddingException::invalidDimensions(
                $context,
                $dimensions,
                FaceMatchingConfig::EMBEDDING_DIMENSIONS
            );
        }

        // Validate all values are numeric
        foreach ($embedding as $value) {
            if (!is_numeric($value)) {
                throw InvalidEmbeddingException::nonNumericValue($context);
            }
        }
    }
}
