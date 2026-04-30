<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFaceEmbedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFaceEmbeddingAccessorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the embedding_array accessor correctly decrypts stored embeddings.
     *
     * @return void
     */
    public function test_embedding_array_accessor_decrypts_stored_embedding(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a 128-dimensional embedding array with varied values
        $originalEmbedding = [];
        for ($i = 0; $i < 128; $i++) {
            $originalEmbedding[] = ($i * 0.01) - 0.5; // Values from -0.5 to 0.77
        }

        // Create and save a UserFaceEmbedding using the mutator
        $userFaceEmbedding = UserFaceEmbedding::create([
            'user_id' => $user->id,
            'embedding_vector' => $originalEmbedding,
        ]);

        // Refresh from database to ensure we're testing persistence
        $userFaceEmbedding->refresh();

        // Use the accessor to get the decrypted array
        $decryptedEmbedding = $userFaceEmbedding->embedding_array;

        // Verify the accessor returns an array
        $this->assertIsArray($decryptedEmbedding);

        // Verify the array has 128 dimensions
        $this->assertCount(128, $decryptedEmbedding);

        // Verify the decrypted values match the original (with floating point tolerance)
        for ($i = 0; $i < 128; $i++) {
            $this->assertEqualsWithDelta($originalEmbedding[$i], $decryptedEmbedding[$i], 0.0001);
        }
    }

    /**
     * Test that the accessor can be used for face matching operations.
     *
     * @return void
     */
    public function test_embedding_array_accessor_can_be_used_for_face_matching(): void
    {
        // Create two users with different embeddings
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create embeddings (simplified for testing)
        $embedding1 = array_fill(0, 128, 0.5);
        $embedding2 = array_fill(0, 128, 0.7);

        $userFaceEmbedding1 = UserFaceEmbedding::create([
            'user_id' => $user1->id,
            'embedding_vector' => $embedding1,
        ]);

        $userFaceEmbedding2 = UserFaceEmbedding::create([
            'user_id' => $user2->id,
            'embedding_vector' => $embedding2,
        ]);

        // Retrieve embeddings using the accessor
        $retrievedEmbedding1 = $userFaceEmbedding1->embedding_array;
        $retrievedEmbedding2 = $userFaceEmbedding2->embedding_array;

        // Verify we can use these for calculations (e.g., Euclidean distance)
        $this->assertIsArray($retrievedEmbedding1);
        $this->assertIsArray($retrievedEmbedding2);
        $this->assertCount(128, $retrievedEmbedding1);
        $this->assertCount(128, $retrievedEmbedding2);

        // Calculate a simple distance metric to verify the arrays are usable
        $distance = 0;
        for ($i = 0; $i < 128; $i++) {
            $distance += pow($retrievedEmbedding1[$i] - $retrievedEmbedding2[$i], 2);
        }
        $distance = sqrt($distance);

        // Verify the distance is reasonable (should be > 0 since embeddings are different)
        $this->assertGreaterThan(0, $distance);
        $this->assertLessThan(10, $distance); // Sanity check
    }
}
