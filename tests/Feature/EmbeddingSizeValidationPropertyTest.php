<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Task 10.1: Embedding Size Validation
 *
 * **Property 16: Embedding Size Validation**
 * **Validates: Requirements 7.1**
 *
 * For any search request with an embedding_vector that is not exactly 128 elements,
 * the validation SHALL fail with a 422 Unprocessable Entity response.
 */
class EmbeddingSizeValidationPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a valid 128-element embedding vector of random floats in [-1, 1].
     */
    private function makeEmbedding(int $size): array
    {
        $vector = [];
        for ($i = 0; $i < $size; $i++) {
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

    // -------------------------------------------------------------------------
    // Property 16 – incorrect sizes MUST fail with 422
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Embedding vectors with sizes other than 128 are rejected.
     *
     * Generates a representative set of incorrect sizes (0, 1, 50, 127, 129, 200, 256)
     * and verifies that each produces a 422 validation error.
     *
     * **Property 16: Embedding Size Validation**
     * **Validates: Requirements 7.1**
     */
    public function test_property_embedding_size_validation_incorrect_sizes_fail(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Test Album',
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(5),
        ]);

        // Representative incorrect sizes covering edge cases and typical wrong values
        $incorrectSizes = [0, 1, 50, 127, 129, 200, 256];

        foreach ($incorrectSizes as $size) {
            $response = $this->postSearch($user, [
                'embedding_vector' => $this->makeEmbedding($size),
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(422, "Expected 422 for embedding size {$size}, got {$response->status()}");

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Response for size {$size} should contain 'errors' key"
            );
            $this->assertArrayHasKey(
                'embedding_vector',
                $json['errors'],
                "Validation errors for size {$size} should reference 'embedding_vector'"
            );
        }
    }

    /**
     * Property-Based Test: Random incorrect sizes (generated) are all rejected.
     *
     * Generates 20 random sizes that are NOT 128 and verifies each returns 422.
     *
     * **Property 16: Embedding Size Validation**
     * **Validates: Requirements 7.1**
     */
    public function test_property_embedding_size_validation_random_incorrect_sizes_fail(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Random Size Album',
            'location'        => 'Bandung',
            'event_date'      => now()->subDays(10),
        ]);

        $testedSizes = [];
        $iterations   = 0;

        // Generate 20 distinct random sizes that are not 128
        while (count($testedSizes) < 20 && $iterations < 1000) {
            $iterations++;
            $size = mt_rand(0, 300);
            if ($size === 128 || in_array($size, $testedSizes, true)) {
                continue;
            }
            $testedSizes[] = $size;

            $response = $this->postSearch($user, [
                'embedding_vector' => $this->makeEmbedding($size),
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(422, "Expected 422 for random embedding size {$size}");

            $json = $response->json();
            $this->assertArrayHasKey(
                'errors',
                $json,
                "Response for random size {$size} should contain 'errors' key"
            );
            $this->assertArrayHasKey(
                'embedding_vector',
                $json['errors'],
                "Validation errors for random size {$size} should reference 'embedding_vector'"
            );
        }

        // Ensure we actually tested 20 distinct sizes
        $this->assertCount(20, $testedSizes, 'Should have tested 20 distinct incorrect sizes');
    }

    /**
     * Property-Based Test: An embedding vector of exactly 128 elements passes size validation.
     *
     * Other validations (e.g. album_id existence) may still fail, but the
     * embedding_vector size error must NOT be present.
     *
     * **Property 16: Embedding Size Validation**
     * **Validates: Requirements 7.1**
     */
    public function test_property_embedding_size_validation_correct_size_passes(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Valid Size Album',
            'location'        => 'Surabaya',
            'event_date'      => now()->subDays(3),
        ]);

        // Run multiple scenarios to confirm the property holds consistently
        for ($scenario = 1; $scenario <= 10; $scenario++) {
            $response = $this->postSearch($user, [
                'embedding_vector' => $this->makeEmbedding(128),
                'album_id'         => $album->id,
            ]);

            // The request should NOT fail due to embedding_vector size.
            // It may succeed (200/2xx) or fail for other reasons (e.g. no matching
            // photos), but the HTTP status must never be 422 caused by the vector size.
            $status = $response->status();

            // Acceptable statuses: 200 (success) or 422 only if the error is NOT
            // about embedding_vector size.
            $this->assertContains(
                $status,
                [200, 422],
                "Scenario {$scenario}: Unexpected HTTP status {$status} for a 128-element vector"
            );

            if ($status === 422) {
                $json = $response->json();
                if (isset($json['errors']['embedding_vector'])) {
                    $sizeErrors = array_filter(
                        $json['errors']['embedding_vector'],
                        fn ($msg) => str_contains(strtolower($msg), 'size') ||
                                     str_contains(strtolower($msg), '128')
                    );
                    $this->assertEmpty(
                        $sizeErrors,
                        "Scenario {$scenario}: A 128-element vector should not produce a size validation error. " .
                        "Got: " . implode(', ', $json['errors']['embedding_vector'])
                    );
                }
            }
        }
    }

    /**
     * Property-Based Test: Empty array (size 0) is rejected.
     *
     * **Property 16: Embedding Size Validation**
     * **Validates: Requirements 7.1**
     */
    public function test_property_embedding_size_validation_empty_array_fails(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Empty Vector Album',
            'location'        => 'Medan',
            'event_date'      => now()->subDays(7),
        ]);

        $response = $this->postSearch($user, [
            'embedding_vector' => [],
            'album_id'         => $album->id,
        ]);

        $response->assertStatus(422);

        $json = $response->json();
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('embedding_vector', $json['errors']);
    }

    /**
     * Property-Based Test: Sizes just below and just above 128 are both rejected.
     *
     * This verifies the boundary condition: only exactly 128 is valid.
     *
     * **Property 16: Embedding Size Validation**
     * **Validates: Requirements 7.1**
     */
    public function test_property_embedding_size_validation_boundary_sizes_fail(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);
        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Boundary Album',
            'location'        => 'Yogyakarta',
            'event_date'      => now()->subDays(2),
        ]);

        foreach ([127, 129] as $size) {
            $response = $this->postSearch($user, [
                'embedding_vector' => $this->makeEmbedding($size),
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(422, "Boundary size {$size} should be rejected with 422");

            $json = $response->json();
            $this->assertArrayHasKey('errors', $json);
            $this->assertArrayHasKey(
                'embedding_vector',
                $json['errors'],
                "Boundary size {$size} should produce an embedding_vector validation error"
            );
        }
    }
}
