<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Integration tests for face scan rate limiting.
 *
 * Validates: Requirement 8.5
 */
class FaceScanRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a valid search payload for the given album.
     */
    private function validPayload(int $albumId): array
    {
        return [
            'embedding_vector' => array_fill(0, 128, 0.5),
            'album_id'         => $albumId,
        ];
    }

    /**
     * Create a test album owned by the given user (or a new one).
     */
    private function createAlbum(?User $photographer = null): Album
    {
        $photographer ??= User::factory()->create();

        return Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Rate Limit Test Album',
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(1),
        ]);
    }

    // -------------------------------------------------------------------------
    // Rate limit enforcement
    // -------------------------------------------------------------------------

    /**
     * The first 10 requests within a minute are allowed (not 429).
     *
     * Validates: Requirement 8.5
     */
    public function test_first_ten_requests_are_allowed(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        // Clear any existing rate limit hits for this user
        RateLimiter::clear('face-scan-search:' . $user->id);

        $payload = $this->validPayload($album->id);

        for ($i = 1; $i <= 10; $i++) {
            $response = $this->actingAs($user)->postJson('/face-scan/search', $payload);

            $this->assertNotEquals(
                429,
                $response->getStatusCode(),
                "Request #{$i} should not be rate-limited (expected < 429, got {$response->getStatusCode()})"
            );
        }
    }

    /**
     * The 11th request within a minute returns 429 Too Many Requests.
     *
     * Validates: Requirement 8.5
     */
    public function test_eleventh_request_returns_429(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        // Clear any existing rate limit hits for this user
        RateLimiter::clear('face-scan-search:' . $user->id);

        $payload = $this->validPayload($album->id);

        // Make 10 allowed requests
        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->postJson('/face-scan/search', $payload);
        }

        // The 11th request must be rejected
        $response = $this->actingAs($user)->postJson('/face-scan/search', $payload);

        $response->assertStatus(429);
    }

    /**
     * The rate limit is per-user: a different user is not affected by another user's requests.
     *
     * Validates: Requirement 8.5
     */
    public function test_rate_limit_is_per_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $album = $this->createAlbum();

        RateLimiter::clear('face-scan-search:' . $userA->id);
        RateLimiter::clear('face-scan-search:' . $userB->id);

        $payload = $this->validPayload($album->id);

        // Exhaust userA's limit
        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($userA)->postJson('/face-scan/search', $payload);
        }

        // userA's 11th request should be 429
        $responseA = $this->actingAs($userA)->postJson('/face-scan/search', $payload);
        $responseA->assertStatus(429);

        // userB's first request should still be allowed
        $responseB = $this->actingAs($userB)->postJson('/face-scan/search', $payload);
        $this->assertNotEquals(
            429,
            $responseB->getStatusCode(),
            'User B should not be rate-limited by User A\'s requests'
        );
    }

    // -------------------------------------------------------------------------
    // Rate limit response format
    // -------------------------------------------------------------------------

    /**
     * The 429 response contains an appropriate error message.
     *
     * Validates: Requirement 8.5
     */
    public function test_rate_limit_response_contains_error_message(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        RateLimiter::clear('face-scan-search:' . $user->id);

        $payload = $this->validPayload($album->id);

        // Exhaust the limit
        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->postJson('/face-scan/search', $payload);
        }

        $response = $this->actingAs($user)->postJson('/face-scan/search', $payload);

        $response->assertStatus(429);

        // Laravel's throttle middleware returns a JSON body with a "message" key for JSON requests
        $response->assertJsonStructure(['message']);
    }

    /**
     * The 429 response includes a Retry-After header indicating when the limit resets.
     *
     * Validates: Requirement 8.5
     */
    public function test_rate_limit_response_includes_retry_after_header(): void
    {
        $user  = User::factory()->create();
        $album = $this->createAlbum();

        RateLimiter::clear('face-scan-search:' . $user->id);

        $payload = $this->validPayload($album->id);

        // Exhaust the limit
        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->postJson('/face-scan/search', $payload);
        }

        $response = $this->actingAs($user)->postJson('/face-scan/search', $payload);

        $response->assertStatus(429);
        $this->assertTrue(
            $response->headers->has('Retry-After') || $response->headers->has('X-RateLimit-Reset'),
            'Response should include Retry-After or X-RateLimit-Reset header'
        );
    }
}
