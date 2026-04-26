<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceScanControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that albums are fetched with photographer relationship.
     * 
     * Validates: Requirements 3.1
     */
    public function test_albums_are_fetched_with_photographer_relationship(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create albums with photographer relationship
        $album1 = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Event 1',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $album2 = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Event 2',
            'location' => 'Bandung',
            'event_date' => now()->subDays(10),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Verify that albums are passed to the view
        $response->assertViewHas('albums');
        
        $albums = $response->viewData('albums');
        
        // Verify that albums collection contains the created albums
        $this->assertCount(2, $albums);
        $this->assertTrue($albums->contains($album1));
        $this->assertTrue($albums->contains($album2));
        
        // Verify that photographer relationship is loaded
        // If the relationship is loaded, accessing it should not trigger additional queries
        foreach ($albums as $album) {
            $this->assertNotNull($album->photographer);
            $this->assertEquals($photographer->id, $album->photographer->id);
        }
    }

    /**
     * Test that view receives albums data.
     * 
     * Validates: Requirements 3.1
     */
    public function test_view_receives_albums_data(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create multiple albums
        $albums = Album::factory(3)->create([
            'photographer_id' => $photographer->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Verify that albums data is passed to the view
        $response->assertViewHas('albums');
        
        $viewAlbums = $response->viewData('albums');
        
        // Verify that all created albums are in the view data
        $this->assertCount(3, $viewAlbums);
        
        // Verify that each album has the expected attributes
        foreach ($viewAlbums as $album) {
            $this->assertNotNull($album->id);
            $this->assertNotNull($album->title);
            $this->assertNotNull($album->location);
            $this->assertNotNull($album->event_date);
            $this->assertNotNull($album->photographer_id);
        }
    }

    /**
     * Test that albums are ordered by event_date in descending order.
     * 
     * Validates: Requirements 3.3
     */
    public function test_albums_are_ordered_by_event_date_descending(): void
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

        $middleAlbum = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Middle Event',
            'location' => 'City C',
            'event_date' => now()->subDays(15),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        $albums = $response->viewData('albums');
        
        // Verify that albums are ordered by event_date descending
        $this->assertEquals($newAlbum->id, $albums[0]->id);
        $this->assertEquals($middleAlbum->id, $albums[1]->id);
        $this->assertEquals($oldAlbum->id, $albums[2]->id);
    }

    /**
     * Test that index method returns the correct view.
     * 
     * Validates: Requirements 3.1
     */
    public function test_index_method_returns_correct_view(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        $response->assertViewIs('face-scan.index');
    }

    /**
     * Test that empty album list is handled correctly.
     * 
     * Validates: Requirements 3.1
     */
    public function test_empty_album_list_is_handled_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Verify that albums data is passed even when empty
        $response->assertViewHas('albums');
        
        $albums = $response->viewData('albums');
        
        // Verify that albums collection is empty
        $this->assertCount(0, $albums);
    }
}
