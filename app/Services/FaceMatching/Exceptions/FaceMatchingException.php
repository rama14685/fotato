<?php

namespace App\Services\FaceMatching\Exceptions;

use Exception;

/**
 * Base exception class for Face Matching Service
 * 
 * All exceptions thrown by the Face Matching Service inherit from this class.
 * Provides context support for detailed error reporting and logging.
 */
class FaceMatchingException extends Exception
{
    /**
     * Additional context information for debugging
     */
    protected array $context = [];

    /**
     * Initialize exception with context
     * 
     * @param string $message Exception message
     * @param int $code Exception code
     * @param Exception|null $previous Previous exception for chaining
     * @param array $context Additional context information
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get context information
     * 
     * @return array Context array with debugging information
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Add context information
     * 
     * @param string $key Context key
     * @param mixed $value Context value
     * @return self For method chaining
     */
    public function withContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
}
