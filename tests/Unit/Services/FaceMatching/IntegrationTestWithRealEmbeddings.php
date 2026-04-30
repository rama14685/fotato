<?php

namespace Tests\Unit\Services\FaceMatching;

use PHPUnit\Framework\TestCase;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use App\Services\FaceMatching\DTOs\MatchResult;

/**
 * Integration Tests for Task 14: Real Face Embeddings
 *
 * **Validates: Requirements 22.1, 22.2, 22.3, 22.4, 22.5**
 *
 * These tests use realistic face embeddings that simulate face-api.js output
 * to verify the service works correctly with actual face recognition data.
 *
 * Test scenarios:
 * - Same person embeddings (high similarity expected > 0.6)
 * - Different people embeddings (low similarity expected < 0.4)
 * - Photos with multiple faces
 * - Complete workflow from customer embedding to filtered results
 */
class IntegrationTestWithRealEmbeddings extends TestCase
{
    private FaceMatchingService $service;

    protected function setUp(): void
    {
        $calculator = new CosineSimilarityCalculator();
        $this->service = new FaceMatchingService($calculator, 0.6);
    }

    /**
     * Task 14.1: Integration test data setup
     * 
     * These embeddings simulate realistic face-api.js output:
     * - 128 dimensions
     * - Values typically in range [-0.5, 0.5]
     * - Normalized vectors (magnitude close to 1.0)
     * - Same person: high similarity (> 0.6)
     * - Different people: low similarity (< 0.4)
     */

    /**
     * Get a realistic customer embedding (Person A - Photo 1)
     * This represents a customer's registered face embedding
     */
    private function getCustomerEmbedding(): array
    {
        // Simulated face-api.js embedding for Person A
        return [
            0.234, -0.156, 0.089, 0.312, -0.201, 0.145, -0.089, 0.267,
            0.178, -0.234, 0.123, 0.089, -0.167, 0.245, 0.134, -0.198,
            0.267, 0.112, -0.189, 0.223, 0.156, -0.134, 0.201, 0.089,
            -0.145, 0.289, 0.167, -0.112, 0.234, 0.098, -0.178, 0.256,
            0.189, -0.223, 0.134, 0.178, -0.156, 0.212, 0.145, -0.189,
            0.267, 0.123, -0.201, 0.189, 0.156, -0.134, 0.223, 0.098,
            -0.167, 0.245, 0.178, -0.123, 0.201, 0.134, -0.189, 0.256,
            0.189, -0.212, 0.145, 0.178, -0.156, 0.234, 0.123, -0.198,
            0.267, 0.145, -0.189, 0.223, 0.167, -0.134, 0.201, 0.112,
            -0.178, 0.256, 0.189, -0.145, 0.212, 0.134, -0.201, 0.245,
            0.178, -0.167, 0.223, 0.156, -0.189, 0.234, 0.145, -0.123,
            0.201, 0.134, -0.178, 0.256, 0.189, -0.212, 0.223, 0.145,
            -0.156, 0.234, 0.178, -0.134, 0.201, 0.167, -0.189, 0.245,
            0.189, -0.223, 0.156, 0.178, -0.145, 0.212, 0.134, -0.198,
            0.267, 0.156, -0.189, 0.223, 0.178, -0.134, 0.201, 0.123,
            -0.167, 0.245, 0.189, -0.156, 0.212, 0.145, -0.201, 0.234
        ];
    }

    /**
     * Get another embedding of the same person (Person A - Photo 2)
     * Should have high similarity with customer embedding (> 0.6)
     * 
     * **Validates: Requirement 22.2**
     */
    private function getSamePersonEmbedding(): array
    {
        // Similar to customer embedding but with slight variations
        // (different lighting, angle, expression)
        return [
            0.245, -0.167, 0.098, 0.323, -0.189, 0.156, -0.078, 0.278,
            0.189, -0.223, 0.134, 0.098, -0.156, 0.256, 0.145, -0.189,
            0.278, 0.123, -0.178, 0.234, 0.167, -0.123, 0.212, 0.098,
            -0.134, 0.298, 0.178, -0.123, 0.245, 0.089, -0.167, 0.267,
            0.198, -0.234, 0.145, 0.189, -0.145, 0.223, 0.156, -0.178,
            0.278, 0.134, -0.189, 0.198, 0.167, -0.123, 0.234, 0.089,
            -0.156, 0.256, 0.189, -0.112, 0.212, 0.145, -0.178, 0.267,
            0.198, -0.223, 0.156, 0.189, -0.145, 0.245, 0.134, -0.189,
            0.278, 0.156, -0.178, 0.234, 0.178, -0.123, 0.212, 0.123,
            -0.167, 0.267, 0.198, -0.134, 0.223, 0.145, -0.189, 0.256,
            0.189, -0.156, 0.234, 0.167, -0.178, 0.245, 0.156, -0.112,
            0.212, 0.145, -0.167, 0.267, 0.198, -0.223, 0.234, 0.156,
            -0.145, 0.245, 0.189, -0.123, 0.212, 0.178, -0.178, 0.256,
            0.198, -0.234, 0.167, 0.189, -0.134, 0.223, 0.145, -0.189,
            0.278, 0.167, -0.178, 0.234, 0.189, -0.123, 0.212, 0.134,
            -0.156, 0.256, 0.198, -0.145, 0.223, 0.156, -0.189, 0.245
        ];
    }

    /**
     * Get embedding of a different person (Person B)
     * Should have low similarity with customer embedding (< 0.4)
     * 
     * **Validates: Requirement 22.3**
     */
    private function getDifferentPersonEmbedding1(): array
    {
        // Completely different facial features
        return [
            -0.189, 0.267, -0.134, 0.089, 0.223, -0.178, 0.145, -0.234,
            -0.123, 0.198, -0.156, 0.234, 0.112, -0.189, 0.267, -0.145,
            -0.089, 0.223, -0.178, 0.134, -0.201, 0.256, -0.167, 0.123,
            0.189, -0.234, 0.178, -0.112, 0.201, -0.156, 0.234, -0.189,
            -0.145, 0.267, -0.123, 0.178, 0.089, -0.223, 0.156, -0.198,
            -0.134, 0.245, -0.189, 0.123, -0.167, 0.234, -0.145, 0.198,
            0.112, -0.223, 0.189, -0.156, 0.234, -0.178, 0.145, -0.201,
            -0.123, 0.256, -0.167, 0.189, 0.134, -0.212, 0.178, -0.145,
            -0.089, 0.234, -0.156, 0.201, -0.134, 0.223, -0.189, 0.167,
            0.145, -0.245, 0.198, -0.123, 0.189, -0.167, 0.234, -0.178,
            -0.112, 0.256, -0.145, 0.201, 0.134, -0.223, 0.189, -0.156,
            -0.089, 0.234, -0.178, 0.167, -0.134, 0.245, -0.189, 0.123,
            0.156, -0.212, 0.198, -0.145, 0.223, -0.167, 0.189, -0.134,
            -0.112, 0.256, -0.178, 0.201, 0.145, -0.234, 0.189, -0.156,
            -0.089, 0.223, -0.167, 0.198, -0.134, 0.245, -0.189, 0.156,
            0.123, -0.212, 0.189, -0.145, 0.234, -0.178, 0.201, -0.167
        ];
    }

    /**
     * Get embedding of another different person (Person C)
     * Should have low similarity with customer embedding (< 0.4)
     * 
     * **Validates: Requirement 22.3**
     */
    private function getDifferentPersonEmbedding2(): array
    {
        // Another person with different features
        return [
            0.089, 0.312, -0.245, 0.167, -0.089, 0.234, -0.178, 0.123,
            0.201, -0.156, 0.289, -0.134, 0.178, -0.223, 0.145, 0.198,
            -0.112, 0.267, -0.189, 0.134, 0.223, -0.167, 0.201, -0.145,
            0.256, -0.189, 0.123, 0.178, -0.134, 0.245, -0.201, 0.167,
            0.134, -0.223, 0.189, -0.145, 0.212, -0.178, 0.156, 0.234,
            -0.123, 0.198, -0.167, 0.245, 0.134, -0.189, 0.223, -0.156,
            0.178, -0.134, 0.256, -0.201, 0.167, 0.189, -0.145, 0.234,
            -0.178, 0.212, -0.156, 0.198, 0.134, -0.223, 0.189, -0.167,
            0.245, -0.189, 0.156, 0.201, -0.134, 0.223, -0.178, 0.145,
            0.198, -0.167, 0.256, -0.189, 0.134, 0.212, -0.145, 0.223,
            -0.178, 0.189, 0.156, -0.134, 0.245, -0.201, 0.178, 0.167,
            -0.145, 0.234, -0.189, 0.156, 0.212, -0.167, 0.198, -0.134,
            0.223, -0.178, 0.189, 0.145, -0.156, 0.256, -0.201, 0.167,
            0.189, -0.134, 0.234, -0.178, 0.156, 0.212, -0.145, 0.223,
            -0.189, 0.167, 0.198, -0.156, 0.245, -0.178, 0.134, 0.201,
            -0.145, 0.223, -0.189, 0.167, 0.234, -0.156, 0.212, -0.178
        ];
    }

    /**
     * Task 14.2.1: Test same person embeddings produce similarity > 0.6
     * 
     * **Validates: Requirements 22.2, 22.5**
     */
    public function test_same_person_embeddings_high_similarity(): void
    {
        $customerEmbedding = $this->getCustomerEmbedding();
        $samePersonEmbedding = $this->getSamePersonEmbedding();

        $photoEmbeddings = [
            new PhotoEmbeddingData(
                photoId: 1001,
                embeddings: [$samePersonEmbedding]
            )
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

        $this->assertCount(1, $results, 'Should return one result');
        $this->assertInstanceOf(MatchResult::class, $results[0]);
        $this->assertEquals(1001, $results[0]->photoId);
        
        // Same person should have similarity > 0.6
        $this->assertGreaterThan(
            0.6,
            $results[0]->similarityScore,
            'Same person embeddings should have similarity > 0.6. Got: ' . $results[0]->similarityScore
        );
        
        $this->assertTrue(
            $results[0]->matchesThreshold,
            'Same person should match the default threshold of 0.6'
        );
    }

    /**
     * Task 14.2.2: Test different people embeddings produce similarity < 0.4
     * 
     * **Validates: Requirements 22.3, 22.5**
     */
    public function test_different_people_embeddings_low_similarity(): void
    {
        $customerEmbedding = $this->getCustomerEmbedding();
        $differentPerson1 = $this->getDifferentPersonEmbedding1();
        $differentPerson2 = $this->getDifferentPersonEmbedding2();

        $photoEmbeddings = [
            new PhotoEmbeddingData(
                photoId: 2001,
                embeddings: [$differentPerson1]
            ),
            new PhotoEmbeddingData(
                photoId: 2002,
                embeddings: [$differentPerson2]
            )
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

        $this->assertCount(2, $results, 'Should return two results');
        
        foreach ($results as $result) {
            $this->assertLessThan(
                0.4,
                $result->similarityScore,
                "Different people should have similarity < 0.4. Photo {$result->photoId} got: {$result->similarityScore}"
            );
            
            $this->assertFalse(
                $result->matchesThreshold,
                "Different people should not match the default threshold of 0.6"
            );
        }
    }

    /**
     * Task 14.2.3: Test photos with multiple faces
     * 
     * **Validates: Requirements 22.4, 22.5**
     */
    public function test_photos_with_multiple_faces(): void
    {
        $customerEmbedding = $this->getCustomerEmbedding();
        $samePersonEmbedding = $this->getSamePersonEmbedding();
        $differentPerson1 = $this->getDifferentPersonEmbedding1();
        $differentPerson2 = $this->getDifferentPersonEmbedding2();

        // Photo 3001: Contains customer (same person) + different person
        // Should match because one face matches
        $photoEmbeddings = [
            new PhotoEmbeddingData(
                photoId: 3001,
                embeddings: [$samePersonEmbedding, $differentPerson1]
            ),
            // Photo 3002: Contains only different people
            // Should not match
            new PhotoEmbeddingData(
                photoId: 3002,
                embeddings: [$differentPerson1, $differentPerson2]
            ),
            // Photo 3003: Contains customer + two different people
            // Should match because one face matches
            new PhotoEmbeddingData(
                photoId: 3003,
                embeddings: [$differentPerson1, $samePersonEmbedding, $differentPerson2]
            )
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

        $this->assertCount(3, $results, 'Should return three results');

        // Find results by photo ID
        $resultsByPhotoId = [];
        foreach ($results as $result) {
            $resultsByPhotoId[$result->photoId] = $result;
        }

        // Photo 3001: Should match (contains same person)
        $this->assertTrue(
            $resultsByPhotoId[3001]->matchesThreshold,
            'Photo with customer face should match'
        );
        $this->assertGreaterThan(
            0.6,
            $resultsByPhotoId[3001]->similarityScore,
            'Photo with customer face should have high similarity'
        );

        // Photo 3002: Should not match (only different people)
        $this->assertFalse(
            $resultsByPhotoId[3002]->matchesThreshold,
            'Photo with only different people should not match'
        );
        $this->assertLessThan(
            0.4,
            $resultsByPhotoId[3002]->similarityScore,
            'Photo with only different people should have low similarity'
        );

        // Photo 3003: Should match (contains same person)
        $this->assertTrue(
            $resultsByPhotoId[3003]->matchesThreshold,
            'Photo with customer face among others should match'
        );
        $this->assertGreaterThan(
            0.6,
            $resultsByPhotoId[3003]->similarityScore,
            'Photo with customer face among others should have high similarity'
        );
    }

    /**
     * Task 14.2.4: Test complete workflow from customer embedding to filtered results
     * 
     * **Validates: Requirements 22.1, 22.2, 22.3, 22.4, 22.5**
     */
    public function test_complete_workflow_with_realistic_embeddings(): void
    {
        $customerEmbedding = $this->getCustomerEmbedding();
        $samePersonEmbedding = $this->getSamePersonEmbedding();
        $differentPerson1 = $this->getDifferentPersonEmbedding1();
        $differentPerson2 = $this->getDifferentPersonEmbedding2();

        // Simulate a realistic album with mix of photos
        $photoEmbeddings = [
            // Photo 4001: Customer alone (should match)
            new PhotoEmbeddingData(
                photoId: 4001,
                embeddings: [$samePersonEmbedding]
            ),
            // Photo 4002: Different person alone (should not match)
            new PhotoEmbeddingData(
                photoId: 4002,
                embeddings: [$differentPerson1]
            ),
            // Photo 4003: Customer + friend (should match)
            new PhotoEmbeddingData(
                photoId: 4003,
                embeddings: [$samePersonEmbedding, $differentPerson1]
            ),
            // Photo 4004: Two different people (should not match)
            new PhotoEmbeddingData(
                photoId: 4004,
                embeddings: [$differentPerson1, $differentPerson2]
            ),
            // Photo 4005: Customer in group photo (should match)
            new PhotoEmbeddingData(
                photoId: 4005,
                embeddings: [$differentPerson1, $samePersonEmbedding, $differentPerson2]
            ),
            // Photo 4006: Another different person (should not match)
            new PhotoEmbeddingData(
                photoId: 4006,
                embeddings: [$differentPerson2]
            )
        ];

        // Execute the complete workflow
        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.6);

        // Verify all photos are processed
        $this->assertCount(6, $results, 'Should return results for all 6 photos');

        // Verify results are sorted by similarity (descending)
        for ($i = 0; $i < count($results) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $results[$i + 1]->similarityScore,
                $results[$i]->similarityScore,
                'Results should be sorted by similarity score in descending order'
            );
        }

        // Filter matching photos (similarity >= 0.6)
        $matchingPhotos = array_filter($results, fn($r) => $r->matchesThreshold);
        $nonMatchingPhotos = array_filter($results, fn($r) => !$r->matchesThreshold);

        // Should have 3 matching photos (4001, 4003, 4005)
        $this->assertCount(3, $matchingPhotos, 'Should have 3 matching photos');
        $this->assertCount(3, $nonMatchingPhotos, 'Should have 3 non-matching photos');

        // Verify matching photo IDs
        $matchingPhotoIds = array_map(fn($r) => $r->photoId, $matchingPhotos);
        $this->assertContains(4001, $matchingPhotoIds, 'Photo 4001 should match');
        $this->assertContains(4003, $matchingPhotoIds, 'Photo 4003 should match');
        $this->assertContains(4005, $matchingPhotoIds, 'Photo 4005 should match');

        // Verify non-matching photo IDs
        $nonMatchingPhotoIds = array_map(fn($r) => $r->photoId, $nonMatchingPhotos);
        $this->assertContains(4002, $nonMatchingPhotoIds, 'Photo 4002 should not match');
        $this->assertContains(4004, $nonMatchingPhotoIds, 'Photo 4004 should not match');
        $this->assertContains(4006, $nonMatchingPhotoIds, 'Photo 4006 should not match');

        // Verify similarity score ranges
        foreach ($matchingPhotos as $photo) {
            $this->assertGreaterThan(
                0.6,
                $photo->similarityScore,
                "Matching photo {$photo->photoId} should have similarity > 0.6"
            );
        }

        foreach ($nonMatchingPhotos as $photo) {
            $this->assertLessThan(
                0.4,
                $photo->similarityScore,
                "Non-matching photo {$photo->photoId} should have similarity < 0.4"
            );
        }
    }

    /**
     * Test with custom threshold override
     * Verifies that threshold can be adjusted for different sensitivity levels
     * 
     * **Validates: Requirements 22.5**
     */
    public function test_realistic_embeddings_with_custom_threshold(): void
    {
        $customerEmbedding = $this->getCustomerEmbedding();
        $samePersonEmbedding = $this->getSamePersonEmbedding();
        $differentPerson1 = $this->getDifferentPersonEmbedding1();

        $photoEmbeddings = [
            new PhotoEmbeddingData(
                photoId: 5001,
                embeddings: [$samePersonEmbedding]
            ),
            new PhotoEmbeddingData(
                photoId: 5002,
                embeddings: [$differentPerson1]
            )
        ];

        // Test with stricter threshold (0.8)
        $resultsStrict = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.8);
        
        // Test with more lenient threshold (0.4)
        $resultsLenient = $this->service->matchFaces($customerEmbedding, $photoEmbeddings, 0.4);

        // Both should return 2 results
        $this->assertCount(2, $resultsStrict);
        $this->assertCount(2, $resultsLenient);

        // With lenient threshold, same person should still match
        $samePersonResultLenient = array_values(array_filter(
            $resultsLenient,
            fn($r) => $r->photoId === 5001
        ))[0];
        $this->assertTrue($samePersonResultLenient->matchesThreshold);

        // Different person should not match even with lenient threshold
        $differentPersonResultLenient = array_values(array_filter(
            $resultsLenient,
            fn($r) => $r->photoId === 5002
        ))[0];
        $this->assertFalse($differentPersonResultLenient->matchesThreshold);
    }

    /**
     * Test edge case: Very similar but not identical embeddings
     * Simulates slight variations in the same person's face
     * 
     * **Validates: Requirements 22.2, 22.5**
     */
    public function test_very_similar_embeddings_boundary_case(): void
    {
        $customerEmbedding = $this->getCustomerEmbedding();
        
        // Create a slightly modified version (simulating same person, different conditions)
        $slightlyModified = $customerEmbedding;
        for ($i = 0; $i < 128; $i++) {
            // Add small random noise (±5%)
            $noise = ($slightlyModified[$i] * 0.05) * (lcg_value() * 2 - 1);
            $slightlyModified[$i] += $noise;
        }

        $photoEmbeddings = [
            new PhotoEmbeddingData(
                photoId: 6001,
                embeddings: [$slightlyModified]
            )
        ];

        $results = $this->service->matchFaces($customerEmbedding, $photoEmbeddings);

        $this->assertCount(1, $results);
        
        // Should still have very high similarity (close to 1.0)
        $this->assertGreaterThan(
            0.9,
            $results[0]->similarityScore,
            'Slightly modified same person should have very high similarity'
        );
        
        $this->assertTrue($results[0]->matchesThreshold);
    }
}
