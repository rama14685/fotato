<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFaceEmbedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFaceEmbeddingForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that face_embedding_id foreign key constraint exists and references user_face_embeddings.id
     */
    public function test_face_embedding_id_foreign_key_constraint_exists(): void
    {
        // Query the database to check for the foreign key constraint
        $foreignKeys = \DB::select("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND COLUMN_NAME = 'face_embedding_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        $this->assertNotEmpty($foreignKeys, 'Foreign key constraint should exist');
        $this->assertEquals('user_face_embeddings', $foreignKeys[0]->REFERENCED_TABLE_NAME);
        $this->assertEquals('id', $foreignKeys[0]->REFERENCED_COLUMN_NAME);
    }

    /**
     * Test that nullOnDelete behavior works correctly
     * When a user_face_embedding is deleted, the user's face_embedding_id should be set to NULL
     */
    public function test_null_on_delete_behavior(): void
    {
        // Create a user
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        // Create a face embedding for the user
        $embedding = UserFaceEmbedding::create([
            'user_id' => $user->id,
            'embedding_vector' => encrypt(json_encode(array_fill(0, 128, 0.5))),
        ]);

        // Link the embedding to the user
        $user->update(['face_embedding_id' => $embedding->id]);
        $user->refresh();

        // Verify the relationship is established
        $this->assertNotNull($user->face_embedding_id);
        $this->assertEquals($embedding->id, $user->face_embedding_id);

        // Delete the face embedding
        $embedding->delete();

        // Refresh the user and verify face_embedding_id is now NULL
        $user->refresh();
        $this->assertNull($user->face_embedding_id, 'face_embedding_id should be NULL after deleting the referenced embedding');
    }

    /**
     * Test that the foreign key constraint prevents invalid face_embedding_id values
     */
    public function test_foreign_key_prevents_invalid_face_embedding_id(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Create a user with an invalid face_embedding_id (non-existent ID)
        User::factory()->create([
            'role' => 'customer',
            'face_embedding_id' => 99999, // Non-existent ID
        ]);
    }

    /**
     * Test that face_embedding_id can be NULL (nullable constraint)
     */
    public function test_face_embedding_id_can_be_null(): void
    {
        // Create a user without a face embedding (e.g., photographer)
        $user = User::factory()->create([
            'role' => 'photographer',
            'face_embedding_id' => null,
        ]);

        $this->assertNull($user->face_embedding_id);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'face_embedding_id' => null,
        ]);
    }
}
