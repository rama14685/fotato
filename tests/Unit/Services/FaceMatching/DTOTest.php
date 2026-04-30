<?php

namespace Tests\Unit\Services\FaceMatching;

use App\Services\FaceMatching\DTOs\MatchResult;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\Exceptions\InvalidEmbeddingException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Data Transfer Objects (DTOs)
 * 
 * Tests PhotoEmbeddingData and MatchResult classes for:
 * - Correct creation and initialization
 * - Validation of input data
 * - Immutability and thread safety
 * - Factory method functionality
 * - Edge cases and error handling
 * 
 * Validates Requirements: 16.1, 16.2, 16.3, 18.4, 9.4
 */
class DTOTest extends TestCase
{
    // ============================================================================
    // PhotoEmbeddingData Tests
    // ============================================================================

    /**
     * Test 1: PhotoEmbeddingData creation with valid integer photo ID
     * 
     * Validates: Requirement 16.1 (Match_Result SHALL contain a photo_id field)
     */
    public function test_photo_embedding_data_creation_with_integer_photo_id(): void
    {
        $photoId = 12345;
        $embeddings = [
            [0.1, 0.2, 0.3, /* ... 125 more dimensions */],
        ];

        $dto = new PhotoEmbeddingData($photoId, $embeddings);

        $this->assertSame($photoId, $dto->photoId);
        $this->assertSame($embeddings, $dto->embeddings);
    }

    /**
     * Test 2: PhotoEmbeddingData creation with valid string photo ID
     * 
     * Validates: Requirement 16.1 (photo_id field can be integer or string)
     */
    public function test_photo_embedding_data_creation_with_string_photo_id(): void
    {
        $photoId = 'photo-uuid-12345';
        $embeddings = [
            [0.1, 0.2, 0.3, /* ... 125 more dimensions */],
        ];

        $dto = new PhotoEmbeddingData($photoId, $embeddings);

        $this->assertSame($photoId, $dto->photoId);
        $this->assertSame($embeddings, $dto->embeddings);
    }

    /**
     * Test 3: PhotoEmbeddingData with multiple face embeddings
     * 
     * Validates: Requirement 9.4 (embeddings array can contain multiple faces)
     */
    public function test_photo_embedding_data_with_multiple_faces(): void
    {
        $photoId = 1001;
        $embeddings = [
            array_fill(0, 128, 0.5),  // Face 1
            array_fill(0, 128, 0.6),  // Face 2
            array_fill(0, 128, 0.7),  // Face 3
        ];

        $dto = new PhotoEmbeddingData($photoId, $embeddings);

        $this->assertCount(3, $dto->embeddings);
        $this->assertSame($embeddings, $dto->embeddings);
    }

    /**
     * Test 4: PhotoEmbeddingData with single face embedding
     * 
     * Validates: Requirement 9.4 (embeddings array structure)
     */
    public function test_photo_embedding_data_with_single_face(): void
    {
        $photoId = 1002;
        $embeddings = [array_fill(0, 128, 0.5)];

        $dto = new PhotoEmbeddingData($photoId, $embeddings);

        $this->assertCount(1, $dto->embeddings);
    }

    /**
     * Test 5: PhotoEmbeddingData throws TypeError for null photo ID
     * 
     * Validates: Requirement 9.4 (photo ID validation via type system)
     */
    public function test_photo_embedding_data_throws_type_error_for_null_photo_id(): void
    {
        $this->expectException(\TypeError::class);

        new PhotoEmbeddingData(null, [array_fill(0, 128, 0.5)]);
    }

    /**
     * Test 6: PhotoEmbeddingData throws exception for empty string photo ID
     * 
     * Validates: Requirement 9.4 (photo ID validation)
     */
    public function test_photo_embedding_data_throws_exception_for_empty_string_photo_id(): void
    {
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Each photo embedding must have a valid photo ID');

        new PhotoEmbeddingData('', [array_fill(0, 128, 0.5)]);
    }

    /**
     * Test 7: PhotoEmbeddingData throws TypeError for non-array embeddings
     * 
     * Validates: Requirement 9.4 (embeddings structure validation via type system)
     */
    public function test_photo_embedding_data_throws_type_error_for_non_array_embeddings(): void
    {
        $this->expectException(\TypeError::class);

        new PhotoEmbeddingData(1001, 'not-an-array');
    }

    /**
     * Test 8: PhotoEmbeddingData throws exception for empty embeddings array
     * 
     * Validates: Requirement 9.4 (embeddings cannot be empty)
     */
    public function test_photo_embedding_data_throws_exception_for_empty_embeddings(): void
    {
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Photo must have at least one embedding');

        new PhotoEmbeddingData(1001, []);
    }

    /**
     * Test 9: PhotoEmbeddingData throws exception for non-array embedding element
     * 
     * Validates: Requirement 9.4 (each embedding must be an array)
     */
    public function test_photo_embedding_data_throws_exception_for_non_array_embedding_element(): void
    {
        $this->expectException(InvalidEmbeddingException::class);
        $this->expectExceptionMessage('Photo embeddings must be an array of numeric arrays');

        new PhotoEmbeddingData(1001, ['not-an-array']);
    }

    /**
     * Test 10: PhotoEmbeddingData is immutable (readonly class)
     * 
     * Validates: Requirement 18.4 (immutable data structures for thread safety)
     */
    public function test_photo_embedding_data_is_immutable(): void
    {
        $photoId = 1001;
        $embeddings = [array_fill(0, 128, 0.5)];
        $dto = new PhotoEmbeddingData($photoId, $embeddings);

        // Verify properties are readonly by attempting to set them
        $this->expectException(\Error::class);
        $dto->photoId = 9999;
    }

    // ============================================================================
    // MatchResult Tests
    // ============================================================================

    /**
     * Test 11: MatchResult creation with integer photo ID
     * 
     * Validates: Requirement 16.1 (Match_Result SHALL contain photo_id field)
     */
    public function test_match_result_creation_with_integer_photo_id(): void
    {
        $photoId = 12345;
        $similarityScore = 0.85;
        $matchesThreshold = true;

        $result = new MatchResult($photoId, $similarityScore, $matchesThreshold);

        $this->assertSame($photoId, $result->photoId);
        $this->assertSame($similarityScore, $result->similarityScore);
        $this->assertTrue($result->matchesThreshold);
    }

    /**
     * Test 12: MatchResult creation with string photo ID
     * 
     * Validates: Requirement 16.1 (photo_id can be integer or string)
     */
    public function test_match_result_creation_with_string_photo_id(): void
    {
        $photoId = 'photo-uuid-12345';
        $similarityScore = 0.75;
        $matchesThreshold = true;

        $result = new MatchResult($photoId, $similarityScore, $matchesThreshold);

        $this->assertSame($photoId, $result->photoId);
    }

    /**
     * Test 13: MatchResult contains all required fields
     * 
     * Validates: Requirements 16.1, 16.2, 16.3 (all required fields present)
     */
    public function test_match_result_contains_all_required_fields(): void
    {
        $result = new MatchResult(1001, 0.85, true);

        $this->assertObjectHasProperty('photoId', $result);
        $this->assertObjectHasProperty('similarityScore', $result);
        $this->assertObjectHasProperty('matchesThreshold', $result);
    }

    /**
     * Test 14: MatchResult factory method creates correct instance
     * 
     * Validates: Requirement 16.2 (static factory method for creating from score and threshold)
     */
    public function test_match_result_factory_method_creates_correct_instance(): void
    {
        $photoId = 1001;
        $similarityScore = 0.75;
        $threshold = 0.6;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertSame($photoId, $result->photoId);
        $this->assertSame($similarityScore, $result->similarityScore);
        $this->assertTrue($result->matchesThreshold);
    }

    /**
     * Test 15: MatchResult factory method sets matchesThreshold to false when score below threshold
     * 
     * Validates: Requirement 16.2 (factory method correctly determines threshold match)
     */
    public function test_match_result_factory_method_sets_matches_threshold_false_when_below(): void
    {
        $photoId = 1001;
        $similarityScore = 0.5;
        $threshold = 0.6;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertFalse($result->matchesThreshold);
    }

    /**
     * Test 16: MatchResult factory method sets matchesThreshold to true when score equals threshold
     * 
     * Validates: Requirement 16.2 (factory method uses >= comparison)
     */
    public function test_match_result_factory_method_sets_matches_threshold_true_when_equal(): void
    {
        $photoId = 1001;
        $similarityScore = 0.6;
        $threshold = 0.6;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertTrue($result->matchesThreshold);
    }

    /**
     * Test 17: MatchResult factory method with negative similarity score
     * 
     * Validates: Requirement 16.2 (factory method handles negative scores)
     */
    public function test_match_result_factory_method_with_negative_similarity(): void
    {
        $photoId = 1001;
        $similarityScore = -0.5;
        $threshold = 0.6;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertSame(-0.5, $result->similarityScore);
        $this->assertFalse($result->matchesThreshold);
    }

    /**
     * Test 18: MatchResult factory method with maximum similarity score
     * 
     * Validates: Requirement 16.2 (factory method handles maximum scores)
     */
    public function test_match_result_factory_method_with_maximum_similarity(): void
    {
        $photoId = 1001;
        $similarityScore = 1.0;
        $threshold = 0.6;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertSame(1.0, $result->similarityScore);
        $this->assertTrue($result->matchesThreshold);
    }

    /**
     * Test 19: MatchResult is immutable (readonly class)
     * 
     * Validates: Requirement 18.4 (immutable data structures for thread safety)
     */
    public function test_match_result_is_immutable(): void
    {
        $result = new MatchResult(1001, 0.85, true);

        // Verify properties are readonly by attempting to set them
        $this->expectException(\Error::class);
        $result->photoId = 9999;
    }

    /**
     * Test 20: MatchResult similarity score accepts float values
     * 
     * Validates: Requirement 16.2 (similarity_score field is float)
     */
    public function test_match_result_similarity_score_accepts_float_values(): void
    {
        $scores = [0.0, 0.5, 0.999999, 1.0, -0.5, -1.0];

        foreach ($scores as $score) {
            $result = new MatchResult(1001, $score, true);
            $this->assertSame($score, $result->similarityScore);
        }
    }

    /**
     * Test 21: MatchResult factory method with zero threshold
     * 
     * Validates: Requirement 16.2 (factory method handles edge case thresholds)
     */
    public function test_match_result_factory_method_with_zero_threshold(): void
    {
        $photoId = 1001;
        $similarityScore = 0.0;
        $threshold = 0.0;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertTrue($result->matchesThreshold);
    }

    /**
     * Test 22: MatchResult factory method with maximum threshold
     * 
     * Validates: Requirement 16.2 (factory method handles edge case thresholds)
     */
    public function test_match_result_factory_method_with_maximum_threshold(): void
    {
        $photoId = 1001;
        $similarityScore = 0.99;
        $threshold = 1.0;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertFalse($result->matchesThreshold);
    }

    /**
     * Test 23: MatchResult factory method with string photo ID
     * 
     * Validates: Requirement 16.2 (factory method accepts string photo IDs)
     */
    public function test_match_result_factory_method_with_string_photo_id(): void
    {
        $photoId = 'photo-uuid-12345';
        $similarityScore = 0.85;
        $threshold = 0.6;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertSame($photoId, $result->photoId);
        $this->assertTrue($result->matchesThreshold);
    }

    /**
     * Test 24: MatchResult factory method produces same result as direct constructor
     * 
     * Validates: Requirement 16.2 (factory method consistency)
     */
    public function test_match_result_factory_method_produces_same_result_as_constructor(): void
    {
        $photoId = 1001;
        $similarityScore = 0.85;
        $threshold = 0.6;

        $factoryResult = MatchResult::create($photoId, $similarityScore, $threshold);
        $constructorResult = new MatchResult($photoId, $similarityScore, true);

        $this->assertSame($factoryResult->photoId, $constructorResult->photoId);
        $this->assertSame($factoryResult->similarityScore, $constructorResult->similarityScore);
        $this->assertSame($factoryResult->matchesThreshold, $constructorResult->matchesThreshold);
    }

    /**
     * Test 25: MatchResult factory method with very small threshold
     * 
     * Validates: Requirement 16.2 (factory method handles precision)
     */
    public function test_match_result_factory_method_with_very_small_threshold(): void
    {
        $photoId = 1001;
        $similarityScore = 0.0001;
        $threshold = 0.00001;

        $result = MatchResult::create($photoId, $similarityScore, $threshold);

        $this->assertTrue($result->matchesThreshold);
    }
}
