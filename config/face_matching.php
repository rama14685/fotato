<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Matching Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Face Matching Service,
    | including similarity thresholds, performance parameters, validation rules,
    | and logging preferences.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Similarity Threshold
    |--------------------------------------------------------------------------
    |
    | The minimum similarity score (0.0-1.0) required for a photo to be
    | considered a match. Higher values require closer matches.
    | Default: 0.6
    |
    */
    'threshold' => env('FACE_MATCHING_THRESHOLD', 0.6),

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that control performance optimization and resource usage
    | for face matching operations.
    |
    */
    'performance' => [
        // Maximum processing time for 1000 photos (seconds)
        'max_processing_time_seconds' => 10,

        // Chunk size for processing large albums
        'chunk_size_large_albums' => 500,

        // Threshold for considering an album "large" (triggers chunked processing)
        'large_album_threshold' => 5000,

        // Memory limit for batch operations (MB)
        'memory_limit_mb' => 512,

        // Number of photos processed before triggering garbage collection
        'gc_trigger_interval' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that control input validation and data integrity checks.
    |
    */
    'validation' => [
        // Required number of dimensions for face embeddings
        'embedding_dimensions' => 128,

        // Allow zero magnitude vectors (return 0.0 similarity)
        'allow_zero_magnitude' => true,

        // Enforce strict numeric validation on embedding values
        'strict_numeric_validation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that control what information is logged during face matching
    | operations. Privacy protection is enforced by default.
    |
    */
    'logging' => [
        // Log warnings when processing time exceeds expected thresholds
        'log_performance_warnings' => true,

        // Log warnings when zero magnitude vectors are detected
        'log_zero_magnitude_warnings' => true,

        // Log when similarity threshold is changed
        'log_threshold_changes' => true,

        // Exclude raw embedding values from logs (privacy protection)
        'exclude_embedding_values' => true,
    ],
];
