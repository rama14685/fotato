<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserFaceEmbedding;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class UserFaceEmbeddingTest extends TestCase
{
    /**
     * Test that UserFaceEmbedding has a belongsTo relationship with User.
     *
     * @return void
     */
    public function test_user_face_embedding_belongs_to_user(): void
    {
        $userFaceEmbedding = new UserFaceEmbedding();
        
        // Verify the relationship exists
        $this->assertTrue(method_exists($userFaceEmbedding, 'user'));
        
        // Verify it's a BelongsTo relationship
        $relation = $userFaceEmbedding->user();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        
        // Verify it points to the User model
        $this->assertInstanceOf(User::class, $relation->getRelated());
        
        // Verify the foreign key is 'user_id'
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    /**
     * Test that the embedding_array accessor decrypts and returns the embedding as an array.
     *
     * @return void
     */
    public function test_embedding_array_accessor_decrypts_embedding(): void
    {
        // Create a 128-dimensional embedding array
        $originalEmbedding = array_fill(0, 128, 0.5);
        
        // Manually encrypt it (simulating what would be stored in the database)
        $encryptedEmbedding = \Illuminate\Support\Facades\Crypt::encryptString(json_encode($originalEmbedding));
        
        // Create a UserFaceEmbedding instance with the encrypted data
        $userFaceEmbedding = new UserFaceEmbedding();
        $userFaceEmbedding->embedding_vector = $encryptedEmbedding;
        
        // Use the accessor to get the decrypted array
        $decryptedEmbedding = $userFaceEmbedding->embedding_array;
        
        // Verify the accessor returns an array
        $this->assertIsArray($decryptedEmbedding);
        
        // Verify the array has 128 dimensions
        $this->assertCount(128, $decryptedEmbedding);
        
        // Verify the decrypted values match the original
        $this->assertEquals($originalEmbedding, $decryptedEmbedding);
    }

    /**
     * Test that the embedding_array accessor works with the mutator (round-trip).
     *
     * @return void
     */
    public function test_embedding_array_accessor_works_with_mutator(): void
    {
        // Create a 128-dimensional embedding array with varied values
        $originalEmbedding = [];
        for ($i = 0; $i < 128; $i++) {
            $originalEmbedding[] = $i * 0.01; // Values from 0.00 to 1.27
        }
        
        // Create a UserFaceEmbedding instance and set the embedding using the mutator
        $userFaceEmbedding = new UserFaceEmbedding();
        $userFaceEmbedding->embedding_vector = $originalEmbedding;
        
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
}
