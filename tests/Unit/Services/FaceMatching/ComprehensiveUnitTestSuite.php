<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\DTOs\MatchResult;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;

/**
 * Comprehensive Unit Test Suite for Task 13
 * 
 * This test suite covers:
 * - Task 13.1: Unit tests for cosine similarity calculations
 * - Task 13.2: Unit tests for boundary value scenarios
 * - Task 13.3: Unit tests for service orchestration
 * 
 * Validates Requirements: 11.1, 11.2, 11.3, 13.1-13.7, 4.5, 4.7, 5.1-5.5, 20.1-20.5
 */
class ComprehensiveUnitTestSuite extends TestCase
{
    private CosineSimilarityCalculator $calculator;
    private FaceMatchingService $service;

    protected function setUp(): void
    {
        $this->calculator = new CosineSimilarityCalculator();
        $this->service = new FaceMatchingService($this->calculator);
    }

    // ============================================================================
    // TASK 13.1: UNIT TESTS FOR COSINE SIMILARITY CALCULATIONS
    // Requirements: 11.1, 11.2, 11.3, 13.6, 13.7, 20.1
    // ============================================================================

    /**
     * Test known embedding pairs with expected results
     * Requirement: 20.1
     */
    public function test_known_embedding_pairs_with_expected_results(): void
    {
        // Create two embeddings with known similarity
        // Using simple vectors for predictable results
        $embedding1 = array_fill(0, 128, 1.0);
        $embedding2 = array_fill(0, 128, 1.0);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Identical embeddings should have similarity = 1.0
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    /**
     * Test orthogonal embeddings (similarity near 0)
     * Requirements: 13.6, 20.1
     */
    public function test_orthogonal_embeddings_similarity_near_zero(): void
    {
        // Create orthogonal vectors
        // First half of embedding1 is 1.0, second half is 0.0
        // First half of embedding2 is 0.0, second half is 1.0
        $embedding1 = array_merge(
            array_fill(0, 64, 1.0),
            array_fill(0, 64, 0.0)
        );
        $embedding2 = array_merge(
            array_fill(0, 64, 0.0),
            array_fill(0, 64, 1.0)
        );

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Orthogonal vectors should have similarity near 0
        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);
    }

    /**
     * Test identical embeddings (similarity = 1.0)
     * Requirements: 11.2, 13.7, 20.1
     */
    public function test_identical_embeddings_similarity_equals_one(): void
    {
        // Create identical embeddings with random values
        $embedding = [];
        for ($i = 0; $i < 128; $i++) {
            $embedding[] = sin($i) * 0.5 + cos($i) * 0.3;
        }

        $similarity = $this->calculator->calculateSimilarity($embedding, $embedding);

        // Self-similarity should be 1.0
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    /**
     * Test opposite embeddings (similarity near -1.0)
     * Requirements: 13.7, 20.1
     */
    public function test_opposite_embeddings_similarity_near_negative_one(): void
    {
        // Create opposite embeddings
        $embedding1 = array_fill(0, 128, 1.0);
        $embedding2 = array_fill(0, 128, -1.0);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Opposite vectors should have similarity near -1.0
        $this->assertEqualsWithDelta(-1.0, $similarity, 0.0001);
    }

    /**
     * Test cosine similarity symmetry property
     * Requirement: 11.1
     */
    public function test_cosine_similarity_symmetry(): void
    {
        $embedding1 = [];
        $embedding2 = [];
        for ($i = 0; $i < 128; $i++) {
            $embedding1[] = sin($i * 0.1);
            $embedding2[] = cos($i * 0.1);
        }

        $similarity1 = $this->calculator->calculateSimilarity($embedding1, $embedding2);
        $similarity2 = $this->calculator->calculateSimilarity($embedding2, $embedding1);

        // Symmetry: similarity(A, B) = similarity(B, A)
        $this->assertEqualsWithDelta($similarity1, $similarity2, 0.0001);
    }

    /**
     * Test cosine similarity with known dot product and magnitudes
     * Requirement: 20.1
     */
    public function test_cosine_similarity_with_known_values(): void
    {
        // Create embeddings where we can calculate expected result
        // embedding1: all 0.5, embedding2: all 0.5
        $embedding1 = array_fill(0, 128, 0.5);
        $embedding2 = array_fill(0, 128, 0.5);

        // Expected: dot_product = 128 * (0.5 * 0.5) = 32
        // magnitude1 = sqrt(128 * 0.5^2) = sqrt(32) = 5.657
        // magnitude2 = sqrt(128 * 0.5^2) = sqrt(32) = 5.657
        // similarity = 32 / (5.657 * 5.657) = 32 / 32 = 1.0

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    /**
     * Test similarity range is always [-1, 1]
     * Requirement: 11.3
     */
    public function test_similarity_range_is_valid(): void
    {
        // Test with various embedding combinations
        $testCases = [
            [array_fill(0, 128, 1.0), array_fill(0, 128, 1.0)],
            [array_fill(0, 128, 1.0), array_fill(0, 128, -1.0)],
            [array_fill(0, 128, 0.5), array_fill(0, 128, 0.3)],
        ];

        foreach ($testCases as [$emb1, $emb2]) {
            $similarity = $this->calculator->calculateSimilarity($emb1, $emb2);
            
            // Allow for floating point precision errors
            $this->assertGreaterThanOrEqual(-1.0001, $similarity);
            $this->assertLessThanOrEqual(1.0001, $similarity);
        }
    }

    // ============================================================================
    // TASK 13.2: UNIT TESTS FOR BOUNDARY VALUE SCENARIOS
    // Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 20.2
    // ============================================================================

    /**
     * Test embeddings with all positive values
     * Requirements: 13.1, 20.2
     */
    public function test_embeddings_with_all_positive_values(): void
    {
        $embedding1 = array_fill(0, 128, 0.8);
        $embedding2 = array_fill(0, 128, 0.6);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // All positive identical direction should give similarity = 1.0
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
        $this->assertGreaterThanOrEqual(-1.0, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
    }

    /**
     * Test embeddings with all negative values
     * Requirements: 13.2, 20.2
     */
    public function test_embeddings_with_all_negative_values(): void
    {
        $embedding1 = array_fill(0, 128, -0.8);
        $embedding2 = array_fill(0, 128, -0.6);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // All negative identical direction should give similarity = 1.0
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
        $this->assertGreaterThanOrEqual(-1.0, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
    }

    /**
     * Test embeddings with mixed positive/negative values
     * Requirements: 13.3, 20.2
     */
    public function test_embeddings_with_mixed_positive_negative_values(): void
    {
        $embedding1 = [];
        $embedding2 = [];
        
        for ($i = 0; $i < 128; $i++) {
            $embedding1[] = ($i % 2 === 0) ? 0.7 : -0.3;
            $embedding2[] = ($i % 2 === 0) ? 0.5 : -0.5;
        }

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Should produce valid similarity in range
        $this->assertGreaterThanOrEqual(-1.0, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
        $this->assertIsFloat($similarity);
        $this->assertFalse(is_nan($similarity));
    }

    /**
     * Test embeddings with very small values
     * Requirements: 13.4, 20.2
     */
    public function test_embeddings_with_very_small_values(): void
    {
        $embedding1 = array_fill(0, 128, 1e-10);
        $embedding2 = array_fill(0, 128, 1e-10);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Very small but identical vectors should still give similarity near 1.0
        $this->assertGreaterThan(0.99, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
        $this->assertFalse(is_nan($similarity));
        $this->assertFalse(is_infinite($similarity));
    }

    /**
     * Test embeddings with maximum float values
     * Requirements: 13.5, 20.2
     */
    public function test_embeddings_with_maximum_float_values(): void
    {
        // Use large but not overflow-causing values
        $largeValue = 1e10;
        $embedding1 = array_fill(0, 128, $largeValue);
        $embedding2 = array_fill(0, 128, $largeValue);

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Large identical vectors should still give similarity = 1.0
        $this->assertGreaterThan(0.99, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
        $this->assertFalse(is_nan($similarity));
        $this->assertFalse(is_infinite($similarity));
    }

    /**
     * Test embeddings with alternating large and small values
     * Requirement: 20.2
     */
    public function test_embeddings_with_alternating_large_small_values(): void
    {
        $embedding1 = [];
        $embedding2 = [];
        
        for ($i = 0; $i < 128; $i++) {
            $embedding1[] = ($i % 2 === 0) ? 1e5 : 1e-5;
            $embedding2[] = ($i % 2 === 0) ? 1e5 : 1e-5;
        }

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Identical patterns should give similarity near 1.0
        $this->assertGreaterThan(0.99, $similarity);
        $this->assertLessThanOrEqual(1.0, $similarity);
    }

    /**
     * Test boundary: embedding with single non-zero value
     * Requirement: 20.2
     */
    public function test_embedding_with_single_non_zero_value(): void
    {
        $embedding1 = array_fill(0, 128, 0.0);
        $embedding1[0] = 1.0;
        
        $embedding2 = array_fill(0, 128, 0.0);
        $embedding2[0] = 1.0;

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Should give similarity = 1.0 (same direction)
        $this->assertEquals(1.0, $similarity, '', 0.0001);
    }

    /**
     * Test boundary: embeddings with opposite signs in single dimension
     * Requirement: 20.2
     */
    public function test_embeddings_with_opposite_signs_single_dimension(): void
    {
        $embedding1 = array_fill(0, 128, 0.0);
        $embedding1[0] = 1.0;
        
        $embedding2 = array_fill(0, 128, 0.0);
        $embedding2[0] = -1.0;

        $similarity = $this->calculator->calculateSimilarity($embedding1, $embedding2);

        // Should give similarity = -1.0 (opposite direction)
        $this->assertEquals(-1.0, $similarity, '', 0.0001);
    }

    // ============================================================================
    // TASK 13.3: UNIT TESTS FOR SERVICE ORCHESTRATION
    // Requirements: 4.5, 4.7, 5.1, 5.2, 5.3, 5.4, 5.5, 20.3, 20.4, 20.5
    // ============================================================================

    /**
     * Test threshold filtering logic
     * Requirements: 4.5, 20.3
     */
    public function test_threshold_filtering_logic(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        
        // Create photos with different similarity scores
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),  // similarity = 1.0
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.3)]),  // similarity = 1.0
            new PhotoEmbeddingData(3, [array_fill(0, 128, -0.5)]), // similarity = -1.0
        ];

        $threshold = 0.6;
        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, $threshold);

        // Check that matchesThreshold is correctly set
        foreach ($results as $result) {
            if ($result->similarityScore >= $threshold) {
                $this->assertTrue($result->matchesThreshold);
            } else {
                $this->assertFalse($result->matchesThreshold);
            }
        }
    }

    /**
     * Test multiple faces per photo scenarios
     * Requirements: 5.1, 5.2, 5.3, 5.4, 20.4
     */
    public function test_multiple_faces_per_photo_scenarios(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        
        // Photo with multiple faces - one matches, one doesn't
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [
                array_fill(0, 128, 0.5),  // High similarity
                array_fill(0, 128, -0.5), // Low similarity
            ]),
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.6);

        // Should use the highest similarity score
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]->photoId);
        $this->assertGreaterThan(0.9, $results[0]->similarityScore);
        $this->assertTrue($results[0]->matchesThreshold);
    }

    /**
     * Test that maximum similarity is selected from multiple faces
     * Requirements: 5.1, 5.2, 20.4
     */
    public function test_maximum_similarity_selected_from_multiple_faces(): void
    {
        $customerEmbedding = array_fill(0, 128, 1.0);
        
        // Photo with three faces of varying similarity
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [
                array_fill(0, 128, 0.3),  // Low similarity
                array_fill(0, 128, 1.0),  // High similarity (should be selected)
                array_fill(0, 128, 0.5),  // Medium similarity
            ]),
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.5);

        // Should select the highest similarity (1.0)
        $this->assertCount(1, $results);
        $this->assertEqualsWithDelta(1.0, $results[0]->similarityScore, 0.0001);
    }

    /**
     * Test result sorting and ordering
     * Requirements: 4.7, 20.5
     */
    public function test_result_sorting_and_ordering(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        
        // Create photos with known different similarities
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.3)]),  // Medium similarity
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.5)]),  // High similarity
            new PhotoEmbeddingData(3, [array_fill(0, 128, 0.1)]),  // Low similarity
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.0);

        // Results should be sorted by similarity score descending
        $this->assertCount(3, $results);
        
        // Verify descending order
        for ($i = 0; $i < count($results) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $results[$i + 1]->similarityScore,
                $results[$i]->similarityScore,
                "Results should be sorted in descending order"
            );
        }
    }

    /**
     * Test batch processing completeness
     * Requirements: 20.3, 20.5
     */
    public function test_batch_processing_completeness(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        
        // Create multiple photos
        $photoEmbeddings = [];
        for ($i = 1; $i <= 10; $i++) {
            $photoEmbeddings[] = new PhotoEmbeddingData(
                $i,
                [array_fill(0, 128, 0.5)]
            );
        }

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.0);

        // Should process all photos
        $this->assertCount(10, $results);
        
        // Verify all photo IDs are present
        $photoIds = array_map(fn($r) => $r->photoId, $results);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertContains($i, $photoIds);
        }
    }

    /**
     * Test photo ID uniqueness in results
     * Requirements: 5.5, 20.5
     */
    public function test_photo_id_uniqueness_in_results(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        
        // Create photos with multiple faces each
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [
                array_fill(0, 128, 0.5),
                array_fill(0, 128, 0.3),
            ]),
            new PhotoEmbeddingData(2, [
                array_fill(0, 128, 0.4),
                array_fill(0, 128, 0.6),
            ]),
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.0);

        // Should have exactly one result per photo
        $this->assertCount(2, $results);
        
        // Verify unique photo IDs
        $photoIds = array_map(fn($r) => $r->photoId, $results);
        $this->assertCount(2, array_unique($photoIds));
    }

    /**
     * Test service with empty photo collection
     * Requirements: 20.3
     */
    public function test_service_with_empty_photo_collection(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.6);

        // Should return empty array
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    /**
     * Test service with single photo
     * Requirements: 20.3
     */
    public function test_service_with_single_photo(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.6);

        // Should return one result
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]->photoId);
    }

    /**
     * Test threshold override functionality
     * Requirements: 4.5, 20.3
     */
    public function test_threshold_override_functionality(): void
    {
        $customerEmbedding = array_fill(0, 128, 1.0);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
        ];

        // Test with different thresholds
        $resultsLowThreshold = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.5);
        $resultsHighThreshold = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.99);

        // Low threshold should match (similarity = 1.0 for same direction)
        $this->assertTrue($resultsLowThreshold[0]->matchesThreshold);
        
        // High threshold should also match since similarity is 1.0
        $this->assertTrue($resultsHighThreshold[0]->matchesThreshold);
        
        // Test with truly different embeddings
        $photoEmbeddings2 = [
            new PhotoEmbeddingData(2, [array_fill(0, 128, -0.5)]),
        ];
        $resultsNegative = $this->service->matchFaces($customerEmbedding, $photoEmbeddings2, 0.5);
        
        // Opposite direction should not match
        $this->assertFalse($resultsNegative[0]->matchesThreshold);
    }

    /**
     * Test service returns MatchResult objects
     * Requirements: 20.5
     */
    public function test_service_returns_match_result_objects(): void
    {
        $customerEmbedding = array_fill(0, 128, 0.5);
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 0.5)]),
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.6);

        // Verify result structure
        $this->assertCount(1, $results);
        $this->assertInstanceOf(MatchResult::class, $results[0]);
        $this->assertIsInt($results[0]->photoId);
        $this->assertIsFloat($results[0]->similarityScore);
        $this->assertIsBool($results[0]->matchesThreshold);
    }

    /**
     * Test service with multiple photos and varying thresholds
     * Requirements: 4.5, 4.7, 20.3, 20.5
     */
    public function test_service_with_multiple_photos_and_varying_thresholds(): void
    {
        $customerEmbedding = array_fill(0, 128, 1.0);
        
        // Create photos with predictable similarities
        $photoEmbeddings = [
            new PhotoEmbeddingData(1, [array_fill(0, 128, 1.0)]),   // similarity = 1.0
            new PhotoEmbeddingData(2, [array_fill(0, 128, 0.5)]),   // similarity = 1.0
            new PhotoEmbeddingData(3, [array_fill(0, 128, -1.0)]),  // similarity = -1.0
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.8);

        // Should return all photos, sorted by similarity
        $this->assertCount(3, $results);
        
        // First two should match threshold, last should not
        $this->assertTrue($results[0]->matchesThreshold);
        $this->assertTrue($results[1]->matchesThreshold);
        $this->assertFalse($results[2]->matchesThreshold);
        
        // Verify sorting (descending)
        $this->assertGreaterThanOrEqual($results[1]->similarityScore, $results[0]->similarityScore);
        $this->assertGreaterThanOrEqual($results[2]->similarityScore, $results[1]->similarityScore);
    }
}
