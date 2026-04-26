<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Unit tests for FaceScanController::index()
 *
 * Validates: Requirements 3.1
 */
class FaceScanControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // 1. Authentication
    // -------------------------------------------------------------------------

    /**
     * Authenticated users can access the face scan index page.
     *
     * Validates: Requirements 3.1, 7.5, 8.4
     */
    public function test_authenticated_user_can_access_face_scan_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();
    }

    /**
     * Unauthenticated users are redirected to the login page.
     *
     * Validates: Requirements 7.5, 8.4
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/face-scan');

        $response->assertRedirect('/login');
    }

    // -------------------------------------------------------------------------
    // 2. Albums fetched with photographer relationship
    // -------------------------------------------------------------------------

    /**
     * The index method fetches albums with the photographer relationship eager-loaded,
     * so no additional queries are fired when accessing album->photographer.
     *
     * Validates: Requirements 3.1
     */
    public function test_albums_are_fetched_with_photographer_relationship_eager_loaded(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            Album::create([
                'photographer_id' => $photographer->id,
                'title' => "Event {$i}",
                'location' => "Location {$i}",
                'event_date' => now()->subDays($i + 1),
            ]);
        }

        // Count queries executed during the request
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();

        // Retrieve the albums variable passed to the view
        $albums = $response->viewData('albums');

        $this->assertNotNull($albums, 'View should receive an $albums variable');
        $this->assertCount(3, $albums);

        // Snapshot query count before accessing relationships
        $queriesBeforeRelationAccess = $queryCount;

        // Access the photographer relationship on every album – should NOT fire new queries
        // because it was eager-loaded
        foreach ($albums as $album) {
            $loadedPhotographer = $album->photographer;
            $this->assertNotNull($loadedPhotographer, 'Photographer relationship should be loaded');
            $this->assertInstanceOf(User::class, $loadedPhotographer);
        }

        // No additional queries should have been fired for the relationship
        $this->assertEquals(
            $queriesBeforeRelationAccess,
            $queryCount,
            'Accessing album->photographer should not fire additional queries (eager loading expected)'
        );
    }

    /**
     * Each album in the view has its photographer relationship already loaded
     * (relationLoaded returns true).
     *
     * Validates: Requirements 3.1
     */
    public function test_photographer_relationship_is_marked_as_loaded_on_each_album(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);
        $user = User::factory()->create();

        Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Album A',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Album B',
            'location' => 'Bandung',
            'event_date' => now()->subDays(10),
        ]);

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();

        $albums = $response->viewData('albums');

        foreach ($albums as $album) {
            $this->assertTrue(
                $album->relationLoaded('photographer'),
                "Album ID {$album->id}: 'photographer' relationship should be eager-loaded"
            );
        }
    }

    /**
     * The photographer relationship returns the correct User model instance.
     *
     * Validates: Requirements 3.1
     */
    public function test_photographer_relationship_returns_correct_user(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer', 'name' => 'Test Photographer']);
        $user = User::factory()->create();

        $album = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Test Album',
            'location' => 'Surabaya',
            'event_date' => now()->subDays(3),
        ]);

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();

        $albums = $response->viewData('albums');
        $viewAlbum = $albums->firstWhere('id', $album->id);

        $this->assertNotNull($viewAlbum);
        $this->assertEquals($photographer->id, $viewAlbum->photographer->id);
        $this->assertEquals('Test Photographer', $viewAlbum->photographer->name);
    }

    // -------------------------------------------------------------------------
    // 3. View receives albums data
    // -------------------------------------------------------------------------

    /**
     * The view receives an $albums variable.
     *
     * Validates: Requirements 3.1
     */
    public function test_view_receives_albums_variable(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();
        $response->assertViewHas('albums');
    }

    /**
     * The $albums variable passed to the view contains all albums in the database.
     *
     * Validates: Requirements 3.1
     */
    public function test_view_receives_all_albums(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);
        $user = User::factory()->create();

        $createdIds = [];
        for ($i = 0; $i < 5; $i++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title' => "Event {$i}",
                'location' => "City {$i}",
                'event_date' => now()->subDays($i + 1),
            ]);
            $createdIds[] = $album->id;
        }

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();

        $viewAlbums = $response->viewData('albums');

        $this->assertCount(5, $viewAlbums);

        foreach ($createdIds as $id) {
            $this->assertTrue(
                $viewAlbums->contains('id', $id),
                "Album ID {$id} should be present in the view data"
            );
        }
    }

    /**
     * When there are no albums, the view receives an empty collection.
     *
     * Validates: Requirements 3.1
     */
    public function test_view_receives_empty_collection_when_no_albums_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();
        $response->assertViewHas('albums');

        $albums = $response->viewData('albums');
        $this->assertCount(0, $albums);
    }

    /**
     * The view is the correct Blade template (face-scan.index).
     *
     * Validates: Requirements 3.1
     */
    public function test_index_returns_correct_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();
        $response->assertViewIs('face-scan.index');
    }

    /**
     * Albums are ordered by event_date descending in the data passed to the view.
     *
     * Validates: Requirements 3.1, 3.3
     */
    public function test_albums_in_view_are_ordered_by_event_date_descending(): void
    {
        $photographer = User::factory()->create(['role' => 'photographer']);
        $user = User::factory()->create();

        $oldest = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Oldest Event',
            'location' => 'City A',
            'event_date' => now()->subDays(30),
        ]);

        $newest = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Newest Event',
            'location' => 'City B',
            'event_date' => now()->subDays(1),
        ]);

        $middle = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Middle Event',
            'location' => 'City C',
            'event_date' => now()->subDays(15),
        ]);

        $response = $this->actingAs($user)->get('/face-scan');

        $response->assertOk();

        $albums = $response->viewData('albums');
        $ids = $albums->pluck('id')->toArray();

        $this->assertEquals(
            [$newest->id, $middle->id, $oldest->id],
            $ids,
            'Albums should be ordered by event_date descending (newest first)'
        );
    }
}
