<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 10.2: Embedding Numeric Validation
 *
 * **Property 17: Embedding Numeric Validation**
 * **Validates: Requirements 7.2**
 *
 * For any search request with an embedding_vector containing non-numeric values,
 * the validation SHALL fail with a 422 Unprocessable Entity response.
 * A vector of exactly 128 numeric floats SHALL pass numeric validation.
 */
class EmbeddingNumericValidationPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a valid 128-element embedding vector of random floats in [-1, 1].
     */
    private function makeNumericEmbedding(): array
    {
        $vector = [];
        for ($i = 0; $i < 128; $i++) {
            $vector[] = (mt_rand(-1000, 1000)) / 1000.0;
        }
        return $vector;
    }

    /**
     * Replace one element at $index in a 128-element numeric vector with $value.
     */
    private function embedWithValueAt(int $index, mixed $value): array
    {
        $vector        = $this->makeNumericEmbedding();
        $vector[$index] = $value;
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
     * Create a test album owned by a photographer user.
     */
    private function createAlbum(): Album
    {
        $photographer = User::factory()->create(['role' => 'photographer']);
        return Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Test Album',
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(5),
        ]);
    }

    /**
     * Assert that the errors array contains at least one key that starts with
     * 'embedding_vector' (covers both 'embedding_vector' and 'embedding_vector.N').
     */
    private function assertHasEmbeddingVectorError(array $errors, string $context = ''): void
    {
        $hasEmbeddingError = false;
        foreach (array_keys($errors) as $key) {
            if (str_starts_with($key, 'embedding_vector')) {
                $hasEmbeddingError = true;
                break;
            }
        }
        $this->assertTrue(
            $hasEmbeddingError,
            ($context ? "{$context}: " : '') .
            "Expected an 'embedding_vector' validation error but got keys: " .
            implode(', ', array_keys($errors))
        );
    }

    /**
     * Assert that the errors array does NOT contain any key that starts with
     * 'embedding_vector' and relates to numeric validation.
     */
    private function assertNoEmbeddingNumericError(array $errors, string $context = ''): void
    {
        foreach ($errors as $key => $messages) {
            if (!str_starts_with($key, 'embedding_vector')) {
                continue;
            }
            foreach ((array) $messages as $msg) {
                $lower = strtolower($msg);
                if (
                    str_contains($lower, 'numeric') ||
                    str_contains($lower, 'integer') ||
                    str_contains($lower, 'number') ||
                    str_contains($lower, 'must be a')
                ) {
                    $this->fail(
                        ($context ? "{$context}: " : '') .
                        "A fully numeric vector should not produce a numeric validation error. Got: {$msg}"
                    );
                }
            }
        }
        $this->assertTrue(true); // mark assertion as executed
    }

    // -------------------------------------------------------------------------
    // Property 17 – non-numeric elements MUST fail with 422
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Embedding vectors containing string values are rejected.
     *
     * Generates 128-element vectors where one element is a non-numeric string
     * and verifies each produces a 422 validation error referencing embedding_vector.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_string_values_fail(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        $stringValues = ['abc', 'hello', 'not-a-number', 'NaN', 'Infinity', '1.2.3'];

        foreach ($stringValues as $strVal) {
            // Place the non-numeric string at a random position in the vector
            $position = mt_rand(0, 127);
            $vector   = $this->embedWithValueAt($position, $strVal);

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(
                422,
                "Expected 422 for embedding containing string '{$strVal}' at position {$position}, got {$response->status()}"
            );

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Response for string value '{$strVal}' should contain 'errors' key"
            );
            $this->assertHasEmbeddingVectorError(
                $json['errors'],
                "String value '{$strVal}' at position {$position}"
            );
        }
    }

    /**
     * Property-Based Test: Embedding vectors containing null values are rejected.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_null_values_fail(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        // Test null at several positions across the vector
        $positions = [0, 1, 63, 64, 126, 127];

        foreach ($positions as $position) {
            $vector = $this->embedWithValueAt($position, null);

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(
                422,
                "Expected 422 for embedding containing null at position {$position}, got {$response->status()}"
            );

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Response for null at position {$position} should contain 'errors' key"
            );
            $this->assertHasEmbeddingVectorError(
                $json['errors'],
                "Null at position {$position}"
            );
        }
    }

    /**
     * Property-Based Test: Embedding vectors containing boolean values are rejected.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_boolean_values_fail(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        foreach ([true, false] as $boolVal) {
            $position = mt_rand(0, 127);
            $vector   = $this->embedWithValueAt($position, $boolVal);

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(
                422,
                "Expected 422 for embedding containing boolean " . ($boolVal ? 'true' : 'false') .
                " at position {$position}, got {$response->status()}"
            );

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Response for boolean value should contain 'errors' key"
            );
            $this->assertHasEmbeddingVectorError(
                $json['errors'],
                "Boolean " . ($boolVal ? 'true' : 'false') . " at position {$position}"
            );
        }
    }

    /**
     * Property-Based Test: Embedding vectors containing nested objects/arrays are rejected.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_object_values_fail(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        $objectValues = [
            ['nested' => 'array'],
            ['x' => 1.0, 'y' => 2.0],
        ];

        foreach ($objectValues as $objVal) {
            $position = mt_rand(0, 127);
            $vector   = $this->embedWithValueAt($position, $objVal);

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(
                422,
                "Expected 422 for embedding containing an object/array at position {$position}, got {$response->status()}"
            );

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Response for object/array value should contain 'errors' key"
            );
            $this->assertHasEmbeddingVectorError(
                $json['errors'],
                "Object/array at position {$position}"
            );
        }
    }

    /**
     * Property-Based Test: Random positions with mixed non-numeric types are all rejected.
     *
     * Generates 20 random scenarios where a single element in a 128-element vector
     * is replaced with a non-numeric value drawn from a pool of invalid types.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_random_non_numeric_positions_fail(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        // Pool of non-numeric values to inject
        $nonNumericPool = [
            'abc',
            'xyz',
            '',
            ' ',
            null,
            'not_a_float',
            'NaN',
            'Infinity',
        ];

        $poolSize = count($nonNumericPool);

        for ($scenario = 1; $scenario <= 20; $scenario++) {
            $position = mt_rand(0, 127);
            $badValue = $nonNumericPool[mt_rand(0, $poolSize - 1)];
            $vector   = $this->embedWithValueAt($position, $badValue);

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(
                422,
                "Scenario {$scenario}: Expected 422 for non-numeric value at position {$position}, got {$response->status()}"
            );

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Scenario {$scenario}: Response should contain 'errors' key"
            );
            $this->assertHasEmbeddingVectorError(
                $json['errors'],
                "Scenario {$scenario}: non-numeric at position {$position}"
            );
        }
    }

    /**
     * Property-Based Test: A vector of exactly 128 numeric floats passes numeric validation.
     *
     * Runs multiple scenarios to confirm that a properly formed numeric vector
     * does NOT produce an embedding_vector numeric validation error.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_all_numeric_passes(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        for ($scenario = 1; $scenario <= 15; $scenario++) {
            $vector = $this->makeNumericEmbedding();

            $response = $this->postSearch($user, [
                'embedding_vector' => $vector,
                'album_id'         => $album->id,
            ]);

            $status = $response->status();

            // The request must NOT fail due to non-numeric embedding elements.
            // Acceptable statuses: 200 (success) or 422 only if the error is NOT
            // about embedding_vector numeric content.
            $this->assertContains(
                $status,
                [200, 422],
                "Scenario {$scenario}: Unexpected HTTP status {$status} for a fully numeric 128-element vector"
            );

            if ($status === 422) {
                $json = $response->json();
                if (isset($json['errors'])) {
                    $this->assertNoEmbeddingNumericError(
                        $json['errors'],
                        "Scenario {$scenario}"
                    );
                }
            }
        }
    }

    /**
     * Property-Based Test: Vectors with all elements as non-numeric strings are rejected.
     *
     * Verifies that even when every element is invalid, the response is still 422
     * with an embedding_vector error.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_all_string_elements_fail(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        // Build a 128-element vector where every element is a non-numeric string
        $allStringVector = array_fill(0, 128, 'not-a-number');

        $response = $this->postSearch($user, [
            'embedding_vector' => $allStringVector,
            'album_id'         => $album->id,
        ]);

        $response->assertStatus(422);

        $json = $response->json();
        $this->assertArrayHasKey('errors', $json);
        $this->assertHasEmbeddingVectorError($json['errors'], 'All-string vector');
    }

    /**
     * Property-Based Test: Integer values (which are numeric) pass numeric validation.
     *
     * Integers are a subset of numeric values and should be accepted.
     *
     * **Property 17: Embedding Numeric Validation**
     * **Validates: Requirements 7.2**
     */
    public function test_property_embedding_numeric_validation_integer_values_pass(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        // Build a 128-element vector of integers (all numeric)
        $intVector = [];
        for ($i = 0; $i < 128; $i++) {
            $intVector[] = mt_rand(-10, 10);
        }

        $response = $this->postSearch($user, [
            'embedding_vector' => $intVector,
            'album_id'         => $album->id,
        ]);

        $status = $response->status();

        $this->assertContains(
            $status,
            [200, 422],
            "Unexpected HTTP status {$status} for an all-integer 128-element vector"
        );

        if ($status === 422) {
            $json = $response->json();
            if (isset($json['errors'])) {
                $this->assertNoEmbeddingNumericError($json['errors'], 'All-integer vector');
            }
        }
    }
}
