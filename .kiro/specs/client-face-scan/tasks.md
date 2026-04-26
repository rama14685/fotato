# Implementation Plan: Client Face Scan

## Overview

This implementation plan breaks down the Client Face Scan feature into discrete coding tasks. The feature enables clients to find their photos in event albums using face recognition technology. The implementation uses Laravel (PHP) for the backend, JavaScript with face-api.js for frontend face detection, and Blade templates for views.

## Tasks

- [x] 1. Set up face-api.js models and frontend infrastructure
  - Download and place face-api.js model files in `public/models` directory (ssdMobilenetv1, faceLandmark68Net, faceRecognitionNet)
  - Create `public/js/face-scan.js` file with model loading function
  - Add face-api.js CDN script tag to layout
  - _Requirements: 2.1, 2.2_

- [x] 1.1 Write unit tests for model loading
  - Test that all three required models load successfully
  - Test error handling when models fail to load
  - _Requirements: 2.1_

- [x] 2. Create face scan Blade view with capture/upload interface
  - Create `resources/views/face-scan/index.blade.php` with app layout
  - Add camera capture button and video/canvas elements
  - Add file upload input with image format restrictions
  - Add image preview element
  - Add album selection dropdown populated from controller
  - Add search button (initially disabled)
  - Add loading indicator and results container
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 3.1, 3.2_

- [x] 2.1 Write integration tests for face scan view
  - Test that view renders with all required UI elements
  - Test that album dropdown is populated correctly
  - Test that search button is initially disabled
  - _Requirements: 1.1, 3.1_

- [x] 3. Implement camera capture functionality
  - Add event listener for camera button to request getUserMedia
  - Display video stream in video element
  - Capture frame to canvas after 3 seconds
  - Stop video stream after capture
  - Call face detection on captured canvas
  - _Requirements: 1.2, 2.1_

- [x] 4. Implement file upload functionality
  - Add event listener for file input change
  - Create image preview from uploaded file
  - Call face detection on uploaded image when loaded
  - _Requirements: 1.3, 1.4, 2.1_

- [x] 5. Implement face detection and embedding extraction
  - Create `extractFaceEmbedding()` function using face-api.js
  - Detect single face with landmarks and descriptor
  - Handle "no face detected" error with user-friendly message
  - Convert descriptor to 128-dimensional array
  - Store embedding in global variable
  - Enable search button when embedding is extracted
  - _Requirements: 2.1, 2.2, 2.3, 2.5, 1.5_

- [x] 5.1 Write property test for embedding extraction
  - **Property 2: Embedding Dimension Consistency**
  - **Validates: Requirements 2.2**
  - Generate random valid face images and verify all extracted embeddings have exactly 128 dimensions
  - _Requirements: 2.2_

- [x] 5.2 Write unit tests for face detection error handling
  - Test error message display when no face is detected
  - Test retry functionality after detection failure
  - _Requirements: 1.5, 2.1_

- [x] 6. Implement search request functionality
  - Add event listener for search button
  - Validate that album is selected before search
  - Send POST request to `/face-scan/search` with embedding_vector and album_id
  - Include CSRF token in request headers
  - Display loading indicator during request
  - Call `displayResults()` with response data
  - _Requirements: 3.4, 3.5, 4.1, 7.3_

- [x] 6.1 Write property test for search request payload
  - **Property 5: Search Request Payload Completeness**
  - **Validates: Requirements 4.1**
  - Generate random search scenarios and verify all requests contain both embedding_vector and album_id
  - _Requirements: 4.1_

- [x] 7. Implement search results display
  - Create `displayResults()` function to render photo grid
  - Display "no matches found" message when results are empty
  - For each photo, render card with watermarked image, similarity percentage, price
  - Add "Add to Cart" button for each photo
  - Implement `addToCart()` function to add photo to cart
  - _Requirements: 6.1, 6.2, 6.4, 6.5, 6.6_

- [x] 7.1 Write property test for result display completeness
  - **Property 14: Photo Display Completeness**
  - **Validates: Requirements 6.2**
  - Generate random matched photos and verify all rendered outputs contain image, similarity, and price
  - _Requirements: 6.2_

- [x] 7.2 Write property test for add to cart button presence
  - **Property 15: Add to Cart Button Presence**
  - **Validates: Requirements 6.5**
  - Generate random matched photos and verify all rendered outputs include "Add to Cart" button
  - _Requirements: 6.5_

- [x] 8. Checkpoint - Ensure frontend functionality works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Create FaceScanController with index method
  - Create `app/Http/Controllers/FaceScanController.php`
  - Implement `index()` method to fetch albums ordered by event_date descending
  - Return face-scan.index view with albums data
  - _Requirements: 3.1, 3.3_

- [x] 9.1 Write property test for album ordering
  - **Property 4: Album Ordering**
  - **Validates: Requirements 3.3**
  - Generate random sets of albums and verify they are always ordered by event_date descending
  - _Requirements: 3.3_

- [x] 9.2 Write unit tests for index method
  - Test that albums are fetched with photographer relationship
  - Test that view receives albums data
  - _Requirements: 3.1_

- [x] 10. Implement search method with validation
  - Add `search()` method to FaceScanController
  - Validate embedding_vector is array with exactly 128 numeric elements
  - Validate album_id exists in albums table
  - Return 422 response with error details on validation failure
  - _Requirements: 4.2, 7.1, 7.2, 7.3, 7.4_

- [x] 10.1 Write property test for embedding size validation
  - **Property 16: Embedding Size Validation**
  - **Validates: Requirements 7.1**
  - Generate random embedding vectors with incorrect sizes and verify validation fails
  - _Requirements: 7.1_

- [x] 10.2 Write property test for embedding numeric validation
  - **Property 17: Embedding Numeric Validation**
  - **Validates: Requirements 7.2**
  - Generate embedding vectors with non-numeric values and verify validation fails
  - _Requirements: 7.2_

- [x] 10.3 Write property test for validation error response format
  - **Property 18: Validation Error Response Format**
  - **Validates: Requirements 7.4**
  - Generate random validation failures and verify all return 422 status with error details
  - _Requirements: 7.4_

- [x] 11. Implement face matching logic in search method
  - Query photos in selected album that have face embeddings
  - Eager load faceEmbedding relationship
  - Loop through each photo and decode embedding_vector from JSON
  - Calculate cosine similarity for each photo embedding
  - Filter photos with similarity > 0.6 (match threshold)
  - Build response array with photo id, watermark_path, price, similarity
  - _Requirements: 4.2, 4.3, 4.4, 5.1, 5.2_

- [x] 11.1 Write property test for similarity calculation completeness
  - **Property 6: Similarity Calculation Completeness**
  - **Validates: Requirements 4.3**
  - Generate albums with N face embeddings and verify similarity is calculated for all N
  - _Requirements: 4.3_

- [x] 11.2 Write property test for threshold filtering
  - **Property 7: Threshold Filtering**
  - **Validates: Requirements 4.4**
  - Generate random search results and verify all returned photos have similarity > 0.6
  - _Requirements: 4.4_

- [x] 12. Implement result sorting and response
  - Sort matched photos by similarity score in descending order using usort
  - Return JSON response with success flag and photos array
  - _Requirements: 4.5, 6.3_

- [x] 12.1 Write property test for result sorting
  - **Property 8: Result Sorting**
  - **Validates: Requirements 4.5, 6.3**
  - Generate random sets of matched photos and verify they are always sorted by similarity descending
  - _Requirements: 4.5, 6.3_

- [x] 13. Implement cosineSimilarity private method
  - Create `cosineSimilarity()` method accepting two arrays
  - Throw InvalidArgumentException if vectors have different dimensions
  - Calculate dot product by summing element-wise products
  - Calculate magnitude for each vector using square root of sum of squares
  - Return 0 if either magnitude is zero
  - Return dot product divided by product of magnitudes
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_

- [x] 13.1 Write property test for cosine similarity symmetry
  - **Property 9: Cosine Similarity Symmetry**
  - **Validates: Requirements 5.3**
  - Generate random pairs of vectors and verify cosineSimilarity(A, B) == cosineSimilarity(B, A)
  - _Requirements: 5.3_

- [x] 13.2 Write property test for cosine similarity range
  - **Property 10: Cosine Similarity Range**
  - **Validates: Requirements 5.6**
  - Generate random pairs of valid vectors and verify similarity is always in [-1, 1]
  - _Requirements: 5.6_

- [x] 13.3 Write property test for cosine similarity identity
  - **Property 11: Cosine Similarity Identity**
  - **Validates: Requirements 5.3**
  - Generate random vectors and verify cosineSimilarity(A, A) == 1.0
  - _Requirements: 5.3_

- [x] 13.4 Write property test for zero vector handling
  - **Property 12: Zero Vector Handling**
  - **Validates: Requirements 5.4**
  - Generate random vectors and verify cosineSimilarity(zero_vector, A) == 0
  - _Requirements: 5.4_

- [x] 13.5 Write property test for dimension mismatch error
  - **Property 13: Dimension Mismatch Error**
  - **Validates: Requirements 5.5**
  - Generate pairs of vectors with different dimensions and verify InvalidArgumentException is thrown
  - _Requirements: 5.5_

- [x] 13.6 Write unit tests for cosineSimilarity edge cases
  - Test with identical vectors (should return 1.0)
  - Test with orthogonal vectors (should return 0)
  - Test with opposite vectors (should return -1.0)
  - Test with zero magnitude vectors
  - _Requirements: 5.3, 5.4, 5.6_

- [x] 14. Add authentication and rate limiting middleware
  - Add routes to `routes/web.php` within auth middleware group
  - Add GET route for `/face-scan` pointing to FaceScanController@index
  - Add POST route for `/face-scan/search` pointing to FaceScanController@search
  - Implement rate limiting (10 requests per minute per user) using throttle middleware
  - _Requirements: 7.5, 8.4, 8.5_

- [x] 14.1 Write integration tests for authentication
  - Test that unauthenticated users cannot access face scan routes
  - Test that authenticated users can access face scan routes
  - _Requirements: 7.5, 8.4_

- [x] 14.2 Write integration tests for rate limiting
  - Test that users are limited to 10 searches per minute
  - Test that rate limit returns appropriate error response
  - _Requirements: 8.5_

- [x] 15. Implement error handling and logging
  - Add try-catch blocks in search method for database and calculation errors
  - Log errors with context (user_id, album_id, error message)
  - Return user-friendly error messages in JSON response
  - Add frontend error handling for failed search requests
  - Display appropriate error messages based on error type
  - _Requirements: 10.3, 10.4, 10.5_

- [x] 15.1 Write property test for error logging completeness
  - **Property 20: Error Logging Completeness**
  - **Validates: Requirements 10.5**
  - Generate random error conditions and verify all are logged with details
  - _Requirements: 10.5_

- [x] 15.2 Write unit tests for error message display
  - Test "Face not detected" message display
  - Test "Camera access denied" message display
  - Test "Search failed" message display
  - Test "Service unavailable" message display
  - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [x] 16. Add database indexing for performance
  - Create migration to add index on `photos.album_id` if not exists
  - Verify index improves query performance for photo retrieval
  - _Requirements: 9.1_

- [x] 16.1 Write performance tests for indexed queries
  - Test that photo queries with album_id use the index
  - Test query performance with large datasets
  - _Requirements: 9.1_

- [x] 17. Implement album caching
  - Add cache for album list in FaceScanController index method
  - Set cache expiration to 1 hour
  - Clear cache when albums are created/updated/deleted
  - _Requirements: 9.2_

- [x] 17.1 Write unit tests for album caching
  - Test that albums are cached after first retrieval
  - Test that cached data is used on subsequent requests
  - Test cache invalidation on album changes
  - _Requirements: 9.2_

- [x] 18. Implement pagination for large result sets
  - Add pagination logic when matched photos exceed 50
  - Return pagination metadata (current_page, total_pages, per_page)
  - Update frontend to handle paginated results
  - Add "Load More" or pagination controls in UI
  - _Requirements: 9.3_

- [x] 18.1 Write integration tests for pagination
  - Test pagination with result sets > 50 photos
  - Test pagination metadata accuracy
  - Test "Load More" functionality
  - _Requirements: 9.3_

- [x] 19. Add HTTPS enforcement and security headers
  - Ensure all routes use HTTPS in production (configure in middleware)
  - Add security headers (X-Frame-Options, X-Content-Type-Options)
  - Verify CSRF token validation is active for POST requests
  - _Requirements: 8.1, 7.5_

- [x] 19.1 Write security tests
  - Test that HTTP requests are redirected to HTTPS in production
  - Test that CSRF token validation prevents unauthorized requests
  - Test that security headers are present in responses
  - _Requirements: 8.1_

- [x] 20. Verify client embedding non-persistence
  - Review code to confirm client embeddings are never saved to database
  - Confirm embeddings are only used in memory during search operation
  - Add code comments documenting privacy design decision
  - _Requirements: 8.2, 8.3_

- [x] 20.1 Write property test for client embedding non-persistence
  - **Property 19: Client Embedding Non-Persistence**
  - **Validates: Requirements 8.2, 8.3**
  - Perform random search operations and verify client embeddings are never in database
  - _Requirements: 8.2, 8.3_

- [-] 21. Final checkpoint - Integration testing and verification
  - Test complete end-to-end flow: capture → detect → select album → search → display results
  - Test with multiple albums and various face photos
  - Test error scenarios (no face, invalid album, network errors)
  - Verify all acceptance criteria are met
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- The implementation uses Laravel (PHP) for backend and JavaScript with face-api.js for frontend
- Face-api.js model files must be downloaded separately and placed in `public/models`
- Cosine similarity calculation is critical for accurate face matching
- Privacy is maintained by never storing client face embeddings in the database
- Performance optimizations (indexing, caching, pagination) ensure scalability
