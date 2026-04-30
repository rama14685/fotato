<?php

namespace App\Services\FaceMatching\DTOs;

use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;

/**
 * Input data transfer object for photo embeddings with metadata
 * 
 * Represents a photo with one or more face embeddings detected in it.
 * 
 * IMMUTABILITY & THREAD SAFETY:
 * - This is a readonly class (PHP 8.2+) - all properties are immutable
 * - Once created, the object cannot be modified
 * - Safe for concurrent usage and sharing between threads
 * - The embeddings array reference is immutable, but callers should not modify array contents
 */
readonly class PhotoEmbeddingData
{
    /**
     * Initialize photo embedding data
     * 
     * @param int|string $photoId Unique identifier for the photo
     * @param array $embeddings Array of 128-dimensional embedding arrays (multiple faces possible)
     * @throws InvalidEmbeddingException If photo ID is invalid or embeddings structure is invalid
     */
    public function __construct(
        public int|string $photoId,
        public array $embeddings
    ) {
        $this->validate();
    }

    /**
     * Validate photo embedding data structure
     * 
     * Ensures photo ID is valid and embeddings array is properly structured.
     * 
     * @return void
     * @throws InvalidEmbeddingException If validation fails
     */
    private function validate(): void
    {
        // Validate photo ID is not null or empty
        if ($this->photoId === null || $this->photoId === '') {
            throw InvalidEmbeddingException::invalidPhotoId();
        }

        // Validate embeddings is an array
        if (!is_array($this->embeddings)) {
            throw InvalidEmbeddingException::invalidEmbeddingsStructure();
        }

        // Validate embeddings array is not empty
        if (empty($this->embeddings)) {
            throw InvalidEmbeddingException::emptyEmbeddings();
        }

        // Validate each embedding is an array
        foreach ($this->embeddings as $embedding) {
            if (!is_array($embedding)) {
                throw InvalidEmbeddingException::invalidEmbeddingsStructure();
            }
        }
    }
}
