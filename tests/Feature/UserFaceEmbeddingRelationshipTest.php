<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFaceEmbedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class UserFaceEmbeddingRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that UserFaceEmbedding can retrieve its associated User.
     *
     * @return void
     */
    public function test_user_face_embedding_can_retrieve_user(): void
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create a face embedding for the user
        $embedding = array_fill(0, 128, 0.5);
        $userFaceEmbedding = UserFaceEmbedding::create([
            'user_id' => $user->id,
            'embedding_vector' => Crypt::encryptString(json_encode($embedding)),
        ]);

        // Verify the relationship works
        $this->assertNotNull($userFaceEmbedding->user);
        $this->assertEquals($user->id, $userFaceEmbedding->user->id);
        $this->assertEquals('Test User', $userFaceEmbedding->user->name);
        $this->assertEquals('test@example.com', $userFaceEmbedding->user->email);
    }

    /**
     * Test that deleting a user cascades to delete the face embedding.
     *
     * @return void
     */
    public function test_deleting_user_cascades_to_face_embedding(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a face embedding for the user
        $embedding = array_fill(0, 128, 0.5);
        $userFaceEmbedding = UserFaceEmbedding::create([
            'user_id' => $user->id,
            'embedding_vector' => Crypt::encryptString(json_encode($embedding)),
        ]);

        $embeddingId = $userFaceEmbedding->id;

        // Delete the user
        $user->delete();

        // Verify the face embedding was also deleted (cascade)
        $this->assertNull(UserFaceEmbedding::find($embeddingId));
    }
}
