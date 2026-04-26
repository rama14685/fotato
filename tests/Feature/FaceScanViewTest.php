<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceScanViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that face scan view renders with all required UI elements.
     * 
     * Validates: Requirements 1.1, 3.1
     */
    public function test_face_scan_view_renders_with_all_required_ui_elements(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Test camera capture button
        $response->assertSee('startCamera', false);
        
        // Test video and canvas elements for camera capture
        $response->assertSee('<video', false);
        $response->assertSee('<canvas', false);
        
        // Test file upload input
        $response->assertSee('uploadFace', false);
        $response->assertSee('type="file"', false);
        
        // Test image preview element
        $response->assertSee('preview', false);
        
        // Test album selection dropdown
        $response->assertSee('albumSelect', false);
        $response->assertSee('<select', false);
        
        // Test search button
        $response->assertSee('searchBtn', false);
        $response->assertSee('disabled', false);
        
        // Test loading indicator
        $response->assertSee('loading', false);
        
        // Test results container
        $response->assertSee('results', false);
    }

    /**
     * Test that album dropdown is populated correctly with albums from database.
     * 
     * Validates: Requirements 1.1, 3.1
     */
    public function test_album_dropdown_is_populated_correctly(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create test albums
        $album1 = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Wedding Event 2024',
            'location' => 'Jakarta',
            'event_date' => now()->subDays(5),
        ]);

        $album2 = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Birthday Party',
            'location' => 'Bandung',
            'event_date' => now()->subDays(10),
        ]);

        $album3 = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Corporate Event',
            'location' => 'Surabaya',
            'event_date' => now()->subDays(2),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Test that all albums are present in the dropdown
        $response->assertSee($album1->title);
        $response->assertSee($album1->location);
        
        $response->assertSee($album2->title);
        $response->assertSee($album2->location);
        
        $response->assertSee($album3->title);
        $response->assertSee($album3->location);
        
        // Test that album IDs are in option values
        $response->assertSee('value="' . $album1->id . '"', false);
        $response->assertSee('value="' . $album2->id . '"', false);
        $response->assertSee('value="' . $album3->id . '"', false);
    }

    /**
     * Test that albums are ordered by event date (most recent first).
     * 
     * Validates: Requirement 3.3
     */
    public function test_albums_are_ordered_by_event_date_descending(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create albums with different dates
        $oldestAlbum = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Oldest Event',
            'location' => 'City A',
            'event_date' => now()->subDays(30),
        ]);

        $newestAlbum = Album::create([
            'photographer_id' => $photographer->id,
            'title' => 'Newest Event',
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
        
        // Get the response content
        $content = $response->getContent();
        
        // Find positions of each album title in the HTML
        $newestPos = strpos($content, $newestAlbum->title);
        $middlePos = strpos($content, $middleAlbum->title);
        $oldestPos = strpos($content, $oldestAlbum->title);
        
        // Assert that newest appears before middle, and middle before oldest
        $this->assertLessThan($middlePos, $newestPos, 'Newest album should appear before middle album');
        $this->assertLessThan($oldestPos, $middlePos, 'Middle album should appear before oldest album');
    }

    /**
     * Test that search button is initially disabled.
     * 
     * Validates: Requirement 3.1
     */
    public function test_search_button_is_initially_disabled(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Test that search button has disabled attribute
        $response->assertSee('id="searchBtn"', false);
        $response->assertSee('disabled', false);
    }

    /**
     * Test that unauthenticated users cannot access face scan page.
     * 
     * Validates: Requirements 7.5, 8.4
     */
    public function test_unauthenticated_users_cannot_access_face_scan(): void
    {
        $response = $this->get('/face-scan');

        $response->assertRedirect('/login');
    }

    /**
     * Test that face scan view includes face-api.js script.
     * 
     * Validates: Requirement 2.1
     */
    public function test_face_scan_view_includes_face_api_script(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Test that face-api.js CDN is included
        $response->assertSee('face-api.js', false);
    }

    /**
     * Test that face scan view includes custom face-scan.js script.
     * 
     * Validates: Requirement 2.1
     */
    public function test_face_scan_view_includes_custom_script(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Test that custom face-scan.js is included
        $response->assertSee('face-scan.js', false);
    }

    /**
     * Test that empty album list displays correctly.
     * 
     * Validates: Requirement 3.1
     */
    public function test_empty_album_list_displays_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Test that album select exists even with no albums
        $response->assertSee('albumSelect', false);
        $response->assertSee('-- Pilih Album --', false);
    }
}
