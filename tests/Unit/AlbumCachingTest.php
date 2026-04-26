<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AlbumCachingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        Cache::flush();
    }

    /**
     * Test that albums are cached after first retrieval.
     * 
     * Validates: Requirements 9.2
     */
    public function test_albums_are_cached_after_first_retrieval(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create test albums
        Album::factory(3)->create([
            'photographer_id' => $photographer->id,
        ]);

        // Verify cache is empty initially
        $this->assertFalse(Cache::has('face_scan_albums'));

        // First request - should populate cache
        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();

        // Verify cache is now populated
        $this->assertTrue(Cache::has('face_scan_albums'));
        
        // Verify cached data matches what was returned
        $cachedAlbums = Cache::get('face_scan_albums');
        $viewAlbums = $response->viewData('albums');
        
        $this->assertCount(3, $cachedAlbums);
        $this->assertEquals($cachedAlbums->pluck('id')->toArray(), $viewAlbums->pluck('id')->toArray());
    }

    /**
     * Test that cached data is used on subsequent requests.
     * 
     * Validates: Requirements 9.2
     */
    public function test_cached_data_is_used_on_subsequent_requests(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create initial albums
        $album1 = Album::factory()->create([
            'photographer_id' => $photographer->id,
            'title' => 'Cached Album',
        ]);

        // First request - populates cache
        $response1 = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response1->assertOk();
        $albums1 = $response1->viewData('albums');
        $this->assertCount(1, $albums1);
        $this->assertEquals('Cached Album', $albums1[0]->title);

        // Create a new album after cache is populated
        $album2 = Album::factory()->create([
            'photographer_id' => $photographer->id,
            'title' => 'New Album After Cache',
        ]);

        // Second request - should use cached data (won't include new album)
        $response2 = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response2->assertOk();
        $albums2 = $response2->viewData('albums');
        
        // Should still only have 1 album (from cache)
        $this->assertCount(1, $albums2);
        $this->assertEquals('Cached Album', $albums2[0]->title);
        
        // Verify the new album is NOT in the cached results
        $this->assertFalse($albums2->contains('id', $album2->id));
    }

    /**
     * Test cache invalidation on album changes (simulate by manually clearing cache).
     * 
     * Validates: Requirements 9.2
     */
    public function test_cache_invalidation_on_album_changes(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create initial album
        $album1 = Album::factory()->create([
            'photographer_id' => $photographer->id,
            'title' => 'Initial Album',
        ]);

        // First request - populates cache
        $response1 = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response1->assertOk();
        $this->assertTrue(Cache::has('face_scan_albums'));
        $albums1 = $response1->viewData('albums');
        $this->assertCount(1, $albums1);

        // Simulate album creation/update/deletion by clearing cache
        // (In production, this would be done in AlbumController)
        Cache::forget('face_scan_albums');
        
        // Create a new album
        $album2 = Album::factory()->create([
            'photographer_id' => $photographer->id,
            'title' => 'New Album',
        ]);

        // Verify cache was cleared
        $this->assertFalse(Cache::has('face_scan_albums'));

        // Second request - should fetch fresh data and repopulate cache
        $response2 = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response2->assertOk();
        $albums2 = $response2->viewData('albums');
        
        // Should now have 2 albums (fresh data)
        $this->assertCount(2, $albums2);
        $this->assertTrue($albums2->contains('id', $album1->id));
        $this->assertTrue($albums2->contains('id', $album2->id));
        
        // Verify cache is repopulated
        $this->assertTrue(Cache::has('face_scan_albums'));
    }

    /**
     * Test that cache expiration is set to 1 hour (3600 seconds).
     * 
     * Validates: Requirements 9.2
     */
    public function test_cache_expiration_is_set_to_one_hour(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        Album::factory()->create([
            'photographer_id' => $photographer->id,
        ]);

        // First request - populates cache
        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();

        // Verify cache exists
        $this->assertTrue(Cache::has('face_scan_albums'));

        // Simulate time passing (travel forward 3599 seconds - just before expiration)
        $this->travel(3599)->seconds();
        
        // Cache should still exist
        $this->assertTrue(Cache::has('face_scan_albums'));

        // Travel forward 2 more seconds (total 3601 seconds - after expiration)
        $this->travel(2)->seconds();
        
        // Cache should now be expired
        $this->assertFalse(Cache::has('face_scan_albums'));
    }

    /**
     * Test that cached albums include photographer relationship.
     * 
     * Validates: Requirements 9.2, 3.1
     */
    public function test_cached_albums_include_photographer_relationship(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        Album::factory()->create([
            'photographer_id' => $photographer->id,
        ]);

        // First request - populates cache
        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();

        // Get cached albums
        $cachedAlbums = Cache::get('face_scan_albums');
        
        // Verify photographer relationship is loaded in cached data
        foreach ($cachedAlbums as $album) {
            $this->assertNotNull($album->photographer);
            $this->assertEquals($photographer->id, $album->photographer->id);
        }
    }

    /**
     * Test that cached albums maintain correct ordering.
     * 
     * Validates: Requirements 9.2, 3.3
     */
    public function test_cached_albums_maintain_correct_ordering(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create albums with specific dates
        $oldAlbum = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Old Event',
            'location' => 'City A',
            'event_date' => now()->subDays(30),
        ]);

        $newAlbum = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'New Event',
            'location' => 'City B',
            'event_date' => now()->subDays(1),
        ]);

        // First request - populates cache
        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();

        // Get cached albums
        $cachedAlbums = Cache::get('face_scan_albums');
        
        // Verify ordering is maintained in cache (descending by event_date)
        $this->assertEquals($newAlbum->id, $cachedAlbums[0]->id);
        $this->assertEquals($oldAlbum->id, $cachedAlbums[1]->id);
    }
}
