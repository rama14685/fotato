<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for face scan authentication.
 *
 * Validates: Requirements 7.5, 8.4
 */
class FaceScanAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /face-scan — unauthenticated access
    // -------------------------------------------------------------------------

    /**
     * Unauthenticated users are redirected away from GET /face-scan.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_unauthenticated_user_cannot_access_face_scan_page(): void
    {
        $response = $this->get('/face-scan');

        $response->assertRedirect('/login');
    }

    /**
     * Unauthenticated GET /face-scan does not return a 200 OK.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_unauthenticated_get_face_scan_does_not_return_200(): void
    {
        $response = $this->get('/face-scan');

        $this->assertNotEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // POST /face-scan/search — unauthenticated access
    // -------------------------------------------------------------------------

    /**
     * Unauthenticated users cannot POST to /face-scan/search.
     * Expects a redirect (302) or 401 Unauthorized.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_unauthenticated_user_cannot_access_face_scan_search(): void
    {
        $payload = [
            'embedding_vector' => array_fill(0, 128, 0.5),
            'album_id'         => 1,
        ];

        $response = $this->postJson('/face-scan/search', $payload);

        // JSON requests receive 401; browser requests receive 302 redirect
        $response->assertStatus(401);
    }

    /**
     * Unauthenticated POST /face-scan/search does not return 200.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_unauthenticated_post_face_scan_search_does_not_return_200(): void
    {
        $payload = [
            'embedding_vector' => array_fill(0, 128, 0.5),
            'album_id'         => 1,
        ];

        $response = $this->postJson('/face-scan/search', $payload);

        $this->assertNotEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /face-scan — authenticated access
    // -------------------------------------------------------------------------

    /**
     * Authenticated users can access GET /face-scan and receive 200 OK.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_authenticated_user_can_access_face_scan_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();
    }

    /**
     * Authenticated users receive the correct view for GET /face-scan.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_authenticated_user_receives_face_scan_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();
        $response->assertViewIs('face-scan.index');
    }

    // -------------------------------------------------------------------------
    // POST /face-scan/search — authenticated access
    // -------------------------------------------------------------------------

    /**
     * Authenticated users can POST to /face-scan/search and receive 200 or 422,
     * but NOT 401 or 403.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_authenticated_user_can_access_face_scan_search(): void
    {
        $photographer = User::factory()->create();
        $user         = User::factory()->create();

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Test Album',
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(1),
        ]);

        $payload = [
            'embedding_vector' => array_fill(0, 128, 0.5),
            'album_id'         => $album->id,
        ];

        $response = $this->actingAs($user)->postJson('/face-scan/search', $payload);

        // Must not be 401 (Unauthorized) or 403 (Forbidden)
        $this->assertNotEquals(401, $response->getStatusCode(), 'Should not return 401 for authenticated user');
        $this->assertNotEquals(403, $response->getStatusCode(), 'Should not return 403 for authenticated user');

        // Expect 200 (success) or 422 (validation error) — both are acceptable
        $this->assertContains(
            $response->getStatusCode(),
            [200, 422],
            "Expected 200 or 422, got {$response->getStatusCode()}"
        );
    }

    /**
     * Authenticated users with a valid payload receive a 200 JSON response from search.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_authenticated_user_with_valid_payload_receives_200_from_search(): void
    {
        $photographer = User::factory()->create();
        $user         = User::factory()->create();

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title'           => 'Test Album',
            'location'        => 'Jakarta',
            'event_date'      => now()->subDays(1),
        ]);

        $payload = [
            'embedding_vector' => array_fill(0, 128, 0.5),
            'album_id'         => $album->id,
        ];

        $response = $this->actingAs($user)->postJson('/face-scan/search', $payload);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'photos']);
    }
}
