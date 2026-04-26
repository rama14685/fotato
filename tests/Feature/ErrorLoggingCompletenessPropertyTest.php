<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\FaceEmbedding;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Property-Based Test for Task 15.1: Error Logging Completeness
 *
 * **Property 20: Error Logging Completeness**
 * **Validates: Requirements 10.5**
 *
 * For any error condition that occurs, the system SHALL log error details
 * for debugging purposes.
 */
class ErrorLoggingCompletenessPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a 128-element embedding vector filled with the given value.
     */
    private function makeUniformEmbedding(float $value): array
    {
        return array_fill(0, 128, $value);
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
    // Property 20 – every error condition is logged with context details
    // -------------------------------------------------------------------------

    /**
     * Property-Based Test: Errors during search are logged with user_id, album_id, and error message.
     *
     * Triggers a database/calculation error by storing a corrupted (non-JSON) embedding
     * in the database, then verifies that Log::error is called with the required context.
     *
     * **Property 20: Error Logging Completeness**
     * **Validates: Requirements 10.5**
     */
    public function test_property_error_is_logged_with_user_id_album_id_and_message(): void
    {
        Log::spy();

        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Error Logging Scenario {$scenario}",
                'location'        => 'Jakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Create a photo with a corrupted embedding (not valid JSON array)
            // json_decode of "not-valid-json" returns null, causing cosineSimilarity
            // to receive null instead of an array, which triggers an error.
            $photo = Photo::create([
                'album_id'       => $album->id,
                'original_path'  => 'photos/original/test.jpg',
                'watermark_path' => 'photos/watermark/test.jpg',
                'price'          => 50000,
            ]);

            FaceEmbedding::create([
                'photo_id'         => $photo->id,
                'embedding_vector' => 'not-valid-json',
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            // The response should be a 500 error
            $response->assertStatus(500);

            $json = $response->json();
            $this->assertFalse($json['success'], "Scenario {$scenario}: success should be false on error");
            $this->assertEquals(
                'Search failed. Please try again',
                $json['message'],
                "Scenario {$scenario}: user-friendly error message should be returned"
            );

            // Verify Log::error was called with the required context keys
            Log::shouldHaveReceived('error')
                ->withArgs(function (string $message, array $context) use ($user, $album) {
                    return $message === 'Face scan search failed'
                        && array_key_exists('user_id', $context)
                        && array_key_exists('album_id', $context)
                        && array_key_exists('error', $context)
                        && $context['user_id'] === $user->id
                        && $context['album_id'] == $album->id;
                });
        }
    }

    /**
     * Property-Based Test: Log contains the error message string for each error condition.
     *
     * Verifies that the 'error' key in the log context is a non-empty string
     * describing what went wrong.
     *
     * **Property 20: Error Logging Completeness**
     * **Validates: Requirements 10.5**
     */
    public function test_property_log_context_error_key_is_non_empty_string(): void
    {
        Log::spy();

        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Error String Scenario {$scenario}",
                'location'        => 'Bandung',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Corrupted embedding triggers an exception
            $photo = Photo::create([
                'album_id'       => $album->id,
                'original_path'  => 'photos/original/test.jpg',
                'watermark_path' => 'photos/watermark/test.jpg',
                'price'          => 50000,
            ]);

            FaceEmbedding::create([
                'photo_id'         => $photo->id,
                'embedding_vector' => 'corrupted-data-' . $scenario,
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(500);

            // Verify the error context contains a non-empty error string
            Log::shouldHaveReceived('error')
                ->withArgs(function (string $message, array $context) {
                    return $message === 'Face scan search failed'
                        && isset($context['error'])
                        && is_string($context['error'])
                        && strlen($context['error']) > 0;
                });
        }
    }

    /**
     * Property-Based Test: Successful searches do NOT trigger error logging.
     *
     * Verifies that Log::error is never called when the search completes normally.
     *
     * **Property 20: Error Logging Completeness**
     * **Validates: Requirements 10.5**
     */
    public function test_property_successful_search_does_not_log_error(): void
    {
        Log::spy();

        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 5; $scenario++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Success Scenario {$scenario}",
                'location'        => 'Surabaya',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            // Create a photo with a valid embedding (identical to client → similarity 1.0)
            $photo = Photo::create([
                'album_id'       => $album->id,
                'original_path'  => 'photos/original/test.jpg',
                'watermark_path' => 'photos/watermark/test.jpg',
                'price'          => 50000,
            ]);

            FaceEmbedding::create([
                'photo_id'         => $photo->id,
                'embedding_vector' => json_encode($clientEmbedding),
            ]);

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(200);

            $json = $response->json();
            $this->assertTrue($json['success'], "Scenario {$scenario}: successful search should return success=true");
        }

        // Log::error should never have been called during successful searches
        Log::shouldNotHaveReceived('error');
    }

    /**
     * Property-Based Test: Multiple error conditions each produce a separate log entry.
     *
     * Triggers N different error conditions and verifies Log::error is called N times,
     * once per error.
     *
     * **Property 20: Error Logging Completeness**
     * **Validates: Requirements 10.5**
     */
    public function test_property_each_error_condition_produces_a_log_entry(): void
    {
        Log::spy();

        $user         = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        $errorCount = rand(2, 6);

        for ($i = 0; $i < $errorCount; $i++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title'           => "Multi Error Album {$i}",
                'location'        => 'Yogyakarta',
                'event_date'      => now()->subDays(rand(1, 365)),
            ]);

            // Corrupted embedding to force an exception
            $photo = Photo::create([
                'album_id'       => $album->id,
                'original_path'  => 'photos/original/test.jpg',
                'watermark_path' => 'photos/watermark/test.jpg',
                'price'          => 50000,
            ]);

            FaceEmbedding::create([
                'photo_id'         => $photo->id,
                'embedding_vector' => 'invalid-json-' . $i,
            ]);

            $clientEmbedding = $this->makeUniformEmbedding(1.0);

            $response = $this->postSearch($user, [
                'embedding_vector' => $clientEmbedding,
                'album_id'         => $album->id,
            ]);

            $response->assertStatus(500);
        }

        // Log::error should have been called exactly once per error condition
        Log::shouldHaveReceived('error')
            ->times($errorCount)
            ->withArgs(function (string $message, array $context) {
                return $message === 'Face scan search failed'
                    && array_key_exists('user_id', $context)
                    && array_key_exists('album_id', $context)
                    && array_key_exists('error', $context);
            });
    }
}
