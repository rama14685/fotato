# UserFaceEmbedding Accessor Usage Guide

## Overview

The `UserFaceEmbedding` model includes an accessor method that automatically decrypts the `embedding_vector` field and returns it as a PHP array. This is essential for face matching operations where the raw embedding values need to be compared.

## Accessor Implementation

The accessor is implemented as `getEmbeddingArrayAttribute()` in the `UserFaceEmbedding` model:

```php
/**
 * Accessor for decrypting embedding_vector
 * Returns the decrypted embedding as an array
 */
public function getEmbeddingArrayAttribute()
{
    return json_decode(Crypt::decryptString($this->embedding_vector), true);
}
```

## Usage

### Basic Usage

To retrieve the decrypted embedding as an array, simply access the `embedding_array` attribute:

```php
$userFaceEmbedding = UserFaceEmbedding::find($id);
$embeddingArray = $userFaceEmbedding->embedding_array;

// $embeddingArray is now a PHP array with 128 float values
// Example: [0.123, -0.456, 0.789, ..., 0.321]
```

### Face Matching Example

Here's how to use the accessor for face matching operations:

```php
// Get the stored user embedding
$userFaceEmbedding = UserFaceEmbedding::where('user_id', $userId)->first();
$storedEmbedding = $userFaceEmbedding->embedding_array;

// Get a new face scan embedding (from face-api.js)
$newEmbedding = $request->input('face_embedding'); // Array of 128 floats

// Calculate Euclidean distance
$distance = 0;
for ($i = 0; $i < 128; $i++) {
    $distance += pow($storedEmbedding[$i] - $newEmbedding[$i], 2);
}
$distance = sqrt($distance);

// Check if faces match (threshold typically 0.6)
$isMatch = $distance < 0.6;
```

### Bulk Face Matching

When searching for a user across multiple stored embeddings:

```php
// Get all user face embeddings
$allEmbeddings = UserFaceEmbedding::all();

// New face scan to match
$scanEmbedding = $request->input('face_embedding');

$bestMatch = null;
$bestDistance = PHP_FLOAT_MAX;

foreach ($allEmbeddings as $userFaceEmbedding) {
    // Use the accessor to get the decrypted array
    $storedEmbedding = $userFaceEmbedding->embedding_array;
    
    // Calculate distance
    $distance = 0;
    for ($i = 0; $i < 128; $i++) {
        $distance += pow($storedEmbedding[$i] - $scanEmbedding[$i], 2);
    }
    $distance = sqrt($distance);
    
    // Track best match
    if ($distance < $bestDistance) {
        $bestDistance = $distance;
        $bestMatch = $userFaceEmbedding;
    }
}

// Check if best match is within threshold
if ($bestDistance < 0.6) {
    $matchedUser = $bestMatch->user;
    // User found!
}
```

## Important Notes

1. **Automatic Decryption**: The accessor automatically handles decryption using Laravel's `Crypt` facade. You don't need to manually decrypt the data.

2. **Array Format**: The accessor returns a standard PHP array (not an object), making it easy to use in mathematical operations.

3. **128 Dimensions**: The returned array always contains exactly 128 float values, as required by face-api.js embeddings.

4. **Performance**: Decryption happens on-demand when you access the `embedding_array` attribute. For bulk operations, consider caching the decrypted values if you need to access them multiple times.

5. **Security**: The raw `embedding_vector` field remains encrypted in the database. Only use the accessor when you need the actual values for comparison.

## Mutator Integration

The model also includes a mutator that automatically encrypts arrays when setting the `embedding_vector`:

```php
// Automatically encrypts the array
$userFaceEmbedding = new UserFaceEmbedding();
$userFaceEmbedding->embedding_vector = [0.123, -0.456, ..., 0.321]; // 128 values
$userFaceEmbedding->save();

// Later, retrieve it decrypted
$decrypted = $userFaceEmbedding->embedding_array; // Same values as original
```

## Testing

The accessor is thoroughly tested with:
- Unit tests for decryption logic
- Integration tests with the mutator (round-trip)
- Feature tests with database persistence
- Face matching operation tests

See `tests/Unit/UserFaceEmbeddingTest.php` and `tests/Feature/UserFaceEmbeddingAccessorTest.php` for examples.
