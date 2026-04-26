<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 10.3: Validation Error Response Format
 *
 * **Property 18: Validation Error Response Format**
 * **Validates: Requirements 7.4**
 *
 * For any validation failure, the system SHALL return a 422 Unprocessable Entity
 * response with error details.
 */
class ValidationErrorResponseFormatPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a valid 128-element embedding vector of random floats in [-1, 1].
     */
    private function makeValidEmbedding(): array
    {
        $vector = [];
        for ($i = 0; $i < 128; $i++) {
            $vector[] = (mt_rand(-1000, 1000)) / 1000.0;
        }
        return $vector;
    }

    /**
     * POST to /face-scan/search as an authenticated user and return the response.
     */
    private function postSearch(User $user, array $payload)
    {
        return $this
            ->actingAs($user)
            ->postJson('/face-scan/search', $payload);
    }

    /**
     * Assert that a response has the correct 422 validation error format.
     * Laravel's default validation response includes 'message' and 'errors' keys.
     */
    private function assertValidationErrorFormat($response, string $context = ''): void
    {
        $prefix = $context ? "{$context}: " : '';

        $response->assertStatus(422, "{$prefix}Expected 422 Unprocessable Entity");

        $json = $response->json();

        $this->assertIsArray($json, "{$prefix}Response body should be a JSON object");

        $this->assertArrayHasKey(
            'message',
            $json,
            "{$prefix}422 response should contain a 'message' key"
        );

        $this->assertArrayHasKey(
            'errors',
            $json,
            "{$prefix}422 response should contain an 'errors' key"
        );

        $this->assertIsArray(
            $json['errors'],
            "{$prefix}'errors' should be an object/array"
        );

        $this->assertNotEmpty(
            $json['errors'],
            "{$prefix}'errors' should not be empty on validation failure"
        );
    }

    // -------------------------------------------------------------------------
    // Property 18 – all validation failures return 422 with error details
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Missing embedding_vector returns 422 with error details.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_missing_embedding_vector(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album        = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Test Album',
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(5),
        ]);

        $response = $this->postSearch($user, [
            // embedding_vector intentionally omitted
            'album_id' => $album->id,
        ]);

        $this->assertValidationErrorFormat($response, 'Missing embedding_vector');

        $json = $response->json();
        $this->assertArrayHasKey(
            'embedding_vector',
            $json['errors'],
            "Errors should reference the missing 'embedding_vector' field"
        );
    }

    /**
     * Property-Based Test: Missing album_id returns 422 with error details.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_missing_album_id(): void
    {
        $user = User::factory()->create();

        $response = $this->postSearch($user, [
            'embedding_vector' => $this->makeValidEmbedding(),
            // album_id intentionally omitted
        ]);

        $this->assertValidationErrorFormat($response, 'Missing album_id');

        $json = $response->json();
        $this->assertArrayHasKey(
            'album_id',
            $json['errors'],
            "Errors should reference the missing 'album_id' field"
        );
    }

    /**
     * Property-Based Test: Non-existent album_id returns 422 with error details.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_nonexistent_album_id(): void
    {
        $user = User::factory()->create();

        $response = $this->postSearch($user, [
            'embedding_vector' => $this->makeValidEmbedding(),
            'album_id'         => 99999, // does not exist
        ]);

        $this->assertValidationErrorFormat($response, 'Non-existent album_id');

        $json = $response->json();
        $this->assertArrayHasKey(
            'album_id',
            $json['errors'],
            "Errors should reference the invalid 'album_id' field"
        );
    }

    /**
     * Property-Based Test: Wrong-size embedding_vector returns 422 with error details.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_wrong_size_embedding(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album        = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Size Test Album',
            'location'        => 'Bandung',
            'event_date'      => now()->subDays(3),
        ]);

        // Test several wrong sizes
        $wrongSizes = [0, 64, 127, 129, 256];

        foreach ($wrongSizes as $size) {
            $vector = [];
            for ($i = 0; $i < $size; $i++) {
                $vector[] = (mt_rand(-1000, 1000)) / 1000.0;
            }

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $this->assertValidationErrorFormat($response, "Wrong size {$size}");

            $json = $response->json();
            $this->assertArrayHasKey(
                'embedding_vector',
                $json['errors'],
                "Errors for size {$size} should reference 'embedding_vector'"
            );
        }
    }

    /**
     * Property-Based Test: Non-numeric embedding elements return 422 with error details.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_non_numeric_embedding(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album        = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Numeric Test Album',
            'location'        => 'Surabaya',
            'event_date'      => now()->subDays(7),
        ]);

        // Build a 128-element vector with one non-numeric element
        $vector     = $this->makeValidEmbedding();
        $vector[42] = 'not-a-number';

        $response = $this->postSearch($user, [
            'embedding_vector' => $vector,
            'album_id'         => $album->id,
        ]);

        $this->assertValidationErrorFormat($response, 'Non-numeric element');

        $json = $response->json();

        // The error key may be 'embedding_vector' or 'embedding_vector.42'
        $hasEmbeddingError = false;
        foreach (array_keys($json['errors']) as $key) {
            if (str_starts_with($key, 'embedding_vector')) {
                $hasEmbeddingError = true;
                break;
            }
        }
        $this->assertTrue(
            $hasEmbeddingError,
            "Errors should reference 'embedding_vector' for non-numeric element"
        );
    }

    /**
     * Property-Based Test: Both fields missing returns 422 with error details for both.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_both_fields_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->postSearch($user, []);

        $this->assertValidationErrorFormat($response, 'Both fields missing');

        $json = $response->json();
        $this->assertArrayHasKey(
            'embedding_vector',
            $json['errors'],
            "Errors should reference missing 'embedding_vector'"
        );
        $this->assertArrayHasKey(
            'album_id',
            $json['errors'],
            "Errors should reference missing 'album_id'"
        );
    }

    /**
     * Property-Based Test: Random validation failures all return 422 with error details.
     *
     * Generates 15 random invalid payloads and verifies each returns a properly
     * formatted 422 response.
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_random_invalid_payloads(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album        = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Random Test Album',
            'location'        => 'Medan',
            'event_date'      => now()->subDays(10),
        ]);

        // Pool of invalid payloads
        $invalidPayloads = [
            // Wrong embedding size
            ['embedding_vector' => array_fill(0, 64, 0.5), 'album_id' => $album->id],
            ['embedding_vector' => array_fill(0, 200, 0.1), 'album_id' => $album->id],
            ['embedding_vector' => [], 'album_id' => $album->id],
            // Non-numeric embedding
            ['embedding_vector' => array_merge(array_fill(0, 127, 0.5), ['bad']), 'album_id' => $album->id],
            // Missing album_id
            ['embedding_vector' => $this->makeValidEmbedding()],
            // Non-existent album_id
            ['embedding_vector' => $this->makeValidEmbedding(), 'album_id' => 99999],
            // album_id as string
            ['embedding_vector' => $this->makeValidEmbedding(), 'album_id' => 'not-an-id'],
            // Both missing
            [],
            // embedding_vector not an array
            ['embedding_vector' => 'not-an-array', 'album_id' => $album->id],
            // embedding_vector as a single number
            ['embedding_vector' => 42, 'album_id' => $album->id],
        ];

        foreach ($invalidPayloads as $index => $payload) {
            $response = $this->postSearch($user, $payload);

            $this->assertValidationErrorFormat($response, "Payload #{$index}");
        }
    }

    /**
     * Property-Based Test: Valid request does NOT return 422.
     *
     * Confirms that a properly formed request passes validation (the response
     * should be 200, not 422).
     *
     * **Property 18: Validation Error Response Format**
     * **Validates: Requirements 7.4**
     */
    public function test_property_validation_error_format_valid_request_passes_validation(): void
    {
        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album        = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Valid Request Album',
            'location'        => 'Yogyakarta',
            'event_date'      => now()->subDays(2),
        ]);

        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $response = $this->postSearch($user, [
                'embedding_vector' => $this->makeValidEmbedding(),
                'album_id'         => $album->id,
            ]);

            $status = $response->status();

            $this->assertNotEquals(
                422,
                $status,
                "Scenario {$scenario}: A valid request should not return 422. Got status {$status}"
            );

            $this->assertEquals(
                200,
                $status,
                "Scenario {$scenario}: A valid request should return 200. Got status {$status}"
            );
        }
    }
}
