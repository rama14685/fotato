<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AlbumOrderingPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property-Based Test for Task 9.1: Album Ordering
     * 
     * **Property 4: Album Ordering**
     * **Validates: Requirements 3.3**
     * 
     * For any set of albums displayed, they SHALL be ordered by event date 
     * in descending order (most recent first).
     */
    public function test_property_album_ordering_descending_by_event_date(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Property-based testing: Generate multiple random scenarios
        for ($scenario = 1; $scenario <= 10; $scenario++) {
            // Clean up albums from previous scenario
            Album::query()->delete();

            // Generate random number of albums (3-15 albums per scenario)
            $albumCount = rand(3, 15);
            $albums = [];
            
            // Generate albums with random event dates
            for ($i = 0; $i < $albumCount; $i++) {
                // Generate random dates within the last 2 years
                $randomDaysAgo = rand(1, 730); // 1 day to 2 years ago
                $eventDate = Carbon::now()->subDays($randomDaysAgo);
                
                $album = Album::create([
                    'photographer_id' => $photographer->id,
                    'title' => "Event {$scenario}-{$i}",
                    'location' => "Location {$i}",
                    'event_date' => $eventDate,
                ]);
                
                $albums[] = $album;
            }

            // Make request to FaceScanController index
            $response = $this
                ->actingAs($user)
                ->get('/face-scan');

            $response->assertOk();

            // Verify the property: albums should be ordered by event_date descending
            $this->assertAlbumsAreOrderedByEventDateDescending($albums, $response, $scenario);
        }
    }

    /**
     * Property-Based Test: Edge case with albums having same event dates
     * 
     * **Property 4: Album Ordering**
     * **Validates: Requirements 3.3**
     */
    public function test_property_album_ordering_with_same_event_dates(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Generate multiple scenarios with albums having same dates
        for ($scenario = 1; $scenario <= 5; $scenario++) {
            Album::query()->delete();

            $baseDate = Carbon::now()->subDays(10);
            
            // Create albums with some having identical event dates
            $albums = [];
            for ($i = 0; $i < 6; $i++) {
                // Some albums have same date, others have different dates
                $eventDate = ($i % 2 === 0) ? $baseDate : $baseDate->copy()->addDays($i);
                
                $album = Album::create([
                    'photographer_id' => $photographer->id,
                    'title' => "Same Date Event {$scenario}-{$i}",
                    'location' => "Location {$i}",
                    'event_date' => $eventDate,
                ]);
                
                $albums[] = $album;
            }

            $response = $this
                ->actingAs($user)
                ->get('/face-scan');

            $response->assertOk();

            // Verify ordering is still maintained (albums with same dates can be in any order among themselves)
            $this->assertAlbumsAreOrderedByEventDateDescending($albums, $response, $scenario);
        }
    }

    /**
     * Property-Based Test: Single album scenario
     * 
     * **Property 4: Album Ordering**
     * **Validates: Requirements 3.3**
     */
    public function test_property_album_ordering_single_album(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Test with single album multiple times
        for ($scenario = 1; $scenario <= 5; $scenario++) {
            Album::query()->delete();

            $randomDaysAgo = rand(1, 365);
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title' => "Single Event {$scenario}",
                'location' => "Single Location",
                'event_date' => Carbon::now()->subDays($randomDaysAgo),
            ]);

            $response = $this
                ->actingAs($user)
                ->get('/face-scan');

            $response->assertOk();

            // Single album should always be displayed correctly
            $response->assertSee($album->title);
            $response->assertSee($album->location);
            $response->assertSee('value="' . $album->id . '"', false);
        }
    }

    /**
     * Property-Based Test: Albums spanning multiple years
     * 
     * **Property 4: Album Ordering**
     * **Validates: Requirements 3.3**
     */
    public function test_property_album_ordering_multiple_years(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        for ($scenario = 1; $scenario <= 3; $scenario++) {
            Album::query()->delete();

            $albums = [];
            
            // Create albums spanning multiple years
            $years = [2020, 2021, 2022, 2023, 2024];
            foreach ($years as $year) {
                for ($month = 1; $month <= 12; $month += 3) { // Every 3 months
                    $eventDate = Carbon::create($year, $month, rand(1, 28));
                    
                    $album = Album::create([
                        'photographer_id' => $photographer->id,
                        'title' => "Event {$year}-{$month}",
                        'location' => "Location {$year}",
                        'event_date' => $eventDate,
                    ]);
                    
                    $albums[] = $album;
                }
            }

            $response = $this
                ->actingAs($user)
                ->get('/face-scan');

            $response->assertOk();

            $this->assertAlbumsAreOrderedByEventDateDescending($albums, $response, $scenario);
        }
    }

    /**
     * Helper method to assert that albums are ordered by event_date descending
     * 
     * @param array $albums The albums that were created
     * @param \Illuminate\Testing\TestResponse $response The HTTP response
     * @param int $scenario The scenario number for debugging
     */
    private function assertAlbumsAreOrderedByEventDateDescending(array $albums, $response, int $scenario): void
    {
        // Get the expected order from database (what the controller should return)
        $expectedOrder = Album::orderBy('event_date', 'desc')->get();
        
        $content = $response->getContent();
        
        // Find positions of each album in the HTML content
        $positions = [];
        foreach ($expectedOrder as $album) {
            $pos = strpos($content, 'value="' . $album->id . '"');
            if ($pos !== false) {
                $positions[] = [
                    'album_id' => $album->id,
                    'title' => $album->title,
                    'event_date' => $album->event_date,
                    'position' => $pos
                ];
            }
        }

        // Verify that positions are in ascending order (earlier in HTML = more recent date)
        for ($i = 0; $i < count($positions) - 1; $i++) {
            $current = $positions[$i];
            $next = $positions[$i + 1];
            
            $this->assertLessThan(
                $next['position'], 
                $current['position'],
                "Scenario {$scenario}: Album '{$current['title']}' (date: {$current['event_date']}) " .
                "should appear before album '{$next['title']}' (date: {$next['event_date']}) in HTML. " .
                "Current position: {$current['position']}, Next position: {$next['position']}"
            );
            
            // Also verify the dates are actually in descending order
            $this->assertGreaterThanOrEqual(
                $next['event_date'],
                $current['event_date'],
                "Scenario {$scenario}: Album '{$current['title']}' event date should be >= " .
                "album '{$next['title']}' event date for proper descending order"
            );
        }

        // Additional verification: ensure all albums are present
        foreach ($expectedOrder as $album) {
            $response->assertSee('value="' . $album->id . '"', false);
            $response->assertSee($album->title);
        }
    }

    /**
     * Property-Based Test: Empty albums scenario
     * 
     * **Property 4: Album Ordering**
     * **Validates: Requirements 3.3**
     */
    public function test_property_album_ordering_empty_albums(): void
    {
        $user = User::factory()->create();

        // Test with no albums
        $response = $this
            ->actingAs($user)
            ->get('/face-scan');

        $response->assertOk();
        
        // Should still render properly with empty album list
        $response->assertSee('albumSelect', false);
        $response->assertSee('-- Pilih Album --', false);
        
        // Should not contain any album option values with actual IDs
        $response->assertDontSee('value="1"', false);
        $response->assertDontSee('value="2"', false);
    }

    /**
     * Property-Based Test: Verify ordering consistency across multiple requests
     * 
     * **Property 4: Album Ordering**
     * **Validates: Requirements 3.3**
     */
    public function test_property_album_ordering_consistency_across_requests(): void
    {
        $user = User::factory()->create();
        $photographer = User::factory()->create(['role' => 'photographer']);

        // Create a set of albums
        $albums = [];
        for ($i = 0; $i < 8; $i++) {
            $album = Album::create([
                'photographer_id' => $photographer->id,
                'title' => "Consistency Test Event {$i}",
                'location' => "Location {$i}",
                'event_date' => Carbon::now()->subDays(rand(1, 100)),
            ]);
            $albums[] = $album;
        }

        // Make multiple requests and verify ordering is consistent
        $firstResponse = $this
            ->actingAs($user)
            ->get('/face-scan');

        $firstResponse->assertOk();
        $firstContent = $firstResponse->getContent();

        // Make several more requests
        for ($request = 1; $request <= 5; $request++) {
            $response = $this
                ->actingAs($user)
                ->get('/face-scan');

            $response->assertOk();
            
            // Extract album order from both responses
            $firstOrder = $this->extractAlbumOrderFromContent($firstContent);
            $currentOrder = $this->extractAlbumOrderFromContent($response->getContent());
            
            $this->assertEquals(
                $firstOrder,
                $currentOrder,
                "Request {$request}: Album ordering should be consistent across multiple requests"
            );
        }
    }

    /**
     * Helper method to extract album order from HTML content
     * 
     * @param string $content HTML content
     * @return array Array of album IDs in order of appearance
     */
    private function extractAlbumOrderFromContent(string $content): array
    {
        preg_match_all('/value="(\d+)"/', $content, $matches);
        return $matches[1] ?? [];
    }
}