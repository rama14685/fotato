# Face Matching Service Provider Guide

## Overview

The `FaceMatchingServiceProvider` registers the Face Matching Service and its dependencies in Laravel's service container, enabling dependency injection and configuration management throughout the application.

## Architecture

The service provider follows Laravel's service provider pattern and implements:

1. **Service Registration**: Registers services as singletons in the container
2. **Configuration Management**: Loads and publishes configuration files
3. **Dependency Injection**: Enables automatic dependency resolution
4. **Thread Safety**: Ensures services are safe for concurrent usage

## Registered Services

### FaceMatchingService

**Class**: `App\Services\FaceMatching\FaceMatchingService`

**Registration**: Singleton

**Dependencies**:
- `CosineSimilarityCalculator` (injected automatically)
- Configuration threshold from `config('face_matching.threshold')`

**Usage**:
```php
use App\Services\FaceMatching\FaceMatchingService;

class PhotoController extends Controller
{
    public function __construct(
        private FaceMatchingService $faceMatchingService
    ) {}
    
    public function searchByFace(Request $request)
    {
        $customerEmbedding = $request->user()->face_embedding;
        $photoEmbeddings = $this->getPhotoEmbeddings($albumId);
        
        $results = $this->faceMatchingService->matchFaces(
            $customerEmbedding,
            $photoEmbeddings
        );
        
        return view('photos.results', ['results' => $results]);
    }
}
```

### CosineSimilarityCalculator

**Class**: `App\Services\FaceMatching\CosineSimilarityCalculator`

**Registration**: Singleton

**Dependencies**: None (stateless pure functions)

**Usage**:
```php
use App\Services\FaceMatching\CosineSimilarityCalculator;

class CustomMatchingService
{
    public function __construct(
        private CosineSimilarityCalculator $calculator
    ) {}
    
    public function calculateSimilarity(array $embeddingA, array $embeddingB): float
    {
        return $this->calculator->calculateSimilarity($embeddingA, $embeddingB);
    }
}
```

## Configuration

### Configuration File

The service provider publishes a configuration file to `config/face_matching.php`:

```php
return [
    'threshold' => env('FACE_MATCHING_THRESHOLD', 0.6),
    
    'performance' => [
        'max_processing_time_seconds' => 10,
        'chunk_size_large_albums' => 500,
        'large_album_threshold' => 5000,
        'memory_limit_mb' => 512,
        'gc_trigger_interval' => 1000,
    ],
    
    'validation' => [
        'embedding_dimensions' => 128,
        'allow_zero_magnitude' => true,
        'strict_numeric_validation' => true,
    ],
    
    'logging' => [
        'log_performance_warnings' => true,
        'log_zero_magnitude_warnings' => true,
        'log_threshold_changes' => true,
        'exclude_embedding_values' => true,
    ],
];
```

### Publishing Configuration

To publish the configuration file for customization:

```bash
php artisan vendor:publish --tag=face-matching-config
```

This creates a copy of the configuration file in your `config` directory that you can customize.

### Environment Variables

You can override the similarity threshold using an environment variable:

```env
FACE_MATCHING_THRESHOLD=0.7
```

## Singleton Pattern

Both services are registered as singletons for performance and thread safety:

### Why Singletons?

1. **Performance**: Avoids creating new instances for each request
2. **Thread Safety**: Both services are stateless and safe to share
3. **Memory Efficiency**: Single instance serves all requests
4. **Configuration Consistency**: Ensures all requests use the same configuration

### Stateless Design

The services are designed to be stateless:

- **No instance variables** store request-specific data
- **All input data** is passed as method parameters
- **All results** are returned as immutable objects
- **Configuration values** (threshold, calculator) are not request-specific

This design ensures that multiple requests can safely use the same service instance concurrently.

## Dependency Injection Examples

### Controller Injection

```php
use App\Services\FaceMatching\FaceMatchingService;

class DashboardController extends Controller
{
    public function __construct(
        private FaceMatchingService $faceMatchingService
    ) {}
    
    public function searchPhotos(Request $request, int $albumId)
    {
        $customerEmbedding = $request->user()->face_embedding;
        $photoEmbeddings = Photo::where('album_id', $albumId)
            ->with('faceEmbeddings')
            ->get()
            ->map(fn($photo) => new PhotoEmbeddingData(
                $photo->id,
                $photo->faceEmbeddings->pluck('embedding')->toArray()
            ))
            ->toArray();
        
        $results = $this->faceMatchingService->matchFaces(
            $customerEmbedding,
            $photoEmbeddings,
            0.7 // Optional: override default threshold
        );
        
        return view('dashboard.photos', ['results' => $results]);
    }
}
```

### Service Injection

```php
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\CosineSimilarityCalculator;

class PhotoMatchingService
{
    public function __construct(
        private FaceMatchingService $faceMatchingService,
        private CosineSimilarityCalculator $calculator
    ) {}
    
    public function findMatchingPhotos(User $user, Album $album): Collection
    {
        $customerEmbedding = $user->face_embedding;
        $photoEmbeddings = $this->getPhotoEmbeddings($album);
        
        $results = $this->faceMatchingService->matchFaces(
            $customerEmbedding,
            $photoEmbeddings
        );
        
        return collect($results)
            ->filter(fn($result) => $result->matchesThreshold)
            ->map(fn($result) => Photo::find($result->photoId));
    }
    
    private function getPhotoEmbeddings(Album $album): array
    {
        return $album->photos()
            ->with('faceEmbeddings')
            ->get()
            ->map(fn($photo) => new PhotoEmbeddingData(
                $photo->id,
                $photo->faceEmbeddings->pluck('embedding')->toArray()
            ))
            ->toArray();
    }
}
```

### Manual Resolution

If you need to resolve services manually:

```php
use App\Services\FaceMatching\FaceMatchingService;

// Resolve from container
$service = app(FaceMatchingService::class);

// Or using the App facade
$service = \Illuminate\Support\Facades\App::make(FaceMatchingService::class);

// Use the service
$results = $service->matchFaces($customerEmbedding, $photoEmbeddings);
```

## Logging Integration

The Face Matching Service integrates with Laravel's logging system:

### Logged Events

1. **Batch Operations**: Start and completion with timing
2. **Chunked Processing**: Large album processing with memory usage
3. **Performance Warnings**: When processing exceeds time targets
4. **Zero Magnitude Warnings**: When invalid embeddings are detected
5. **Error Context**: Full context for debugging (privacy-safe)

### Log Channels

The service uses Laravel's default log channel. You can configure logging in `config/logging.php`:

```php
'channels' => [
    'face_matching' => [
        'driver' => 'daily',
        'path' => storage_path('logs/face_matching.log'),
        'level' => 'info',
        'days' => 14,
    ],
],
```

Then update the service to use this channel:

```php
use Illuminate\Support\Facades\Log;

Log::channel('face_matching')->info('Face matching operation started');
```

## Performance Considerations

### Singleton Benefits

- **Memory**: Single instance shared across all requests
- **Initialization**: Service initialized once at application boot
- **Configuration**: Configuration loaded once and cached

### Thread Safety

Both services are thread-safe because:

1. **No Shared State**: No instance variables store request data
2. **Immutable Results**: All results are immutable objects
3. **Pure Functions**: Calculator uses pure mathematical functions
4. **Independent Operations**: Each method call is independent

### Concurrent Usage

Multiple requests can safely use the same service instance:

```php
// Request 1
$results1 = $service->matchFaces($customer1Embedding, $photos1);

// Request 2 (concurrent)
$results2 = $service->matchFaces($customer2Embedding, $photos2);

// Both operations are independent and safe
```

## Testing

### Unit Testing with Service Provider

```php
use Tests\TestCase;
use App\Services\FaceMatching\FaceMatchingService;

class MyFeatureTest extends TestCase
{
    public function test_face_matching_integration()
    {
        // Service is automatically available via container
        $service = $this->app->make(FaceMatchingService::class);
        
        // Use the service in your test
        $results = $service->matchFaces($customerEmbedding, $photoEmbeddings);
        
        $this->assertNotEmpty($results);
    }
}
```

### Mocking Services

```php
use App\Services\FaceMatching\FaceMatchingService;
use Mockery;

public function test_with_mocked_service()
{
    // Create mock
    $mock = Mockery::mock(FaceMatchingService::class);
    $mock->shouldReceive('matchFaces')
        ->once()
        ->andReturn([/* mock results */]);
    
    // Replace in container
    $this->app->instance(FaceMatchingService::class, $mock);
    
    // Test your code that uses the service
}
```

## Troubleshooting

### Service Not Found

If you get "Class not found" errors:

1. Clear configuration cache: `php artisan config:clear`
2. Clear application cache: `php artisan cache:clear`
3. Verify provider is registered in `bootstrap/providers.php`

### Configuration Not Loading

If configuration changes aren't applied:

1. Clear config cache: `php artisan config:clear`
2. Verify config file exists: `config/face_matching.php`
3. Check environment variables in `.env`

### Singleton Issues

If you need a fresh instance (testing):

```php
// Clear singleton binding
$this->app->forgetInstance(FaceMatchingService::class);

// Create new instance
$service = $this->app->make(FaceMatchingService::class);
```

## Requirements Validation

This service provider implementation satisfies the following requirements:

- ✅ **Requirement 6.1**: Configuration stored in application config
- ✅ **Requirement 6.6**: Configured threshold applied to all operations
- ✅ **Requirement 10.1**: Error logging with full context
- ✅ **Requirement 10.2**: Zero magnitude warnings logged
- ✅ **Requirement 10.3**: Batch operation logging with timing
- ✅ **Requirement 18.1**: Stateless service design
- ✅ **Requirement 18.2**: Thread-safe concurrent usage

## Additional Resources

- [Laravel Service Providers Documentation](https://laravel.com/docs/providers)
- [Laravel Service Container Documentation](https://laravel.com/docs/container)
- [Face Matching Service Guide](./FaceMatchingServiceGuide.md)
