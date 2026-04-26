# Requirements Document: Client Face Scan

## Introduction

Fitur **Client Face Scan** memungkinkan pembeli untuk menemukan foto mereka sendiri di sebuah event dengan melakukan scan wajah menggunakan kamera atau upload foto, kemudian sistem mencocokkan wajah tersebut dengan foto-foto dalam album menggunakan face recognition technology.

## Glossary

- **System**: Aplikasi web photography marketplace dengan fitur face recognition
- **Client**: Pembeli/user yang mencari foto mereka di album event
- **Face_Scan_Module**: Komponen frontend yang menangani capture/upload foto wajah dan ekstraksi embedding
- **Face_Matching_Service**: Service backend yang menghitung similarity antara face embeddings
- **Embedding_Vector**: Array 128-dimensional yang merepresentasikan fitur wajah
- **Similarity_Score**: Nilai cosine similarity antara dua embedding vectors (range -1 to 1)
- **Match_Threshold**: Nilai minimum similarity (0.6) untuk menganggap foto sebagai match
- **Album**: Koleksi foto dari satu event photography
- **Face_Embedding**: Data embedding vector yang tersimpan untuk setiap wajah dalam foto

## Requirements

### Requirement 1: Face Capture Interface

**User Story:** As a client, I want to capture or upload my face photo, so that the system can search for photos containing my face.

#### Acceptance Criteria

1. WHEN a client accesses the face scan page, THE System SHALL display options to capture via camera or upload a file
2. WHEN a client clicks the camera capture button, THE System SHALL activate the device camera and display video preview
3. WHEN a client uploads an image file, THE System SHALL accept common image formats (JPEG, PNG, WebP)
4. WHEN a face photo is captured or uploaded, THE Face_Scan_Module SHALL display a preview of the image
5. IF no face is detected in the uploaded/captured image, THEN THE System SHALL display an error message and allow retry

### Requirement 2: Face Detection and Embedding Extraction

**User Story:** As a client, I want my face to be detected and processed automatically, so that I don't need to manually crop or adjust the photo.

#### Acceptance Criteria

1. WHEN a face photo is provided, THE Face_Scan_Module SHALL detect faces using face-api.js library
2. WHEN a face is detected, THE Face_Scan_Module SHALL extract a 128-dimensional embedding vector
3. IF multiple faces are detected, THEN THE Face_Scan_Module SHALL use the largest/most prominent face
4. THE Face_Scan_Module SHALL complete face detection and embedding extraction within 5 seconds
5. WHEN embedding extraction is complete, THE System SHALL enable the search button

### Requirement 3: Album Selection

**User Story:** As a client, I want to select which event/album to search in, so that I can find photos from a specific event.

#### Acceptance Criteria

1. WHEN a client accesses the face scan page, THE System SHALL display a list of available albums
2. THE System SHALL display album information including title, location, and event date
3. WHEN displaying albums, THE System SHALL order them by event date (most recent first)
4. THE System SHALL require album selection before allowing search
5. WHEN a client selects an album, THE System SHALL store the selection for the search request

### Requirement 4: Face Matching and Search

**User Story:** As a client, I want the system to find photos containing my face, so that I can purchase photos of myself from the event.

#### Acceptance Criteria

1. WHEN a client initiates search, THE System SHALL send the embedding vector and album ID to the backend
2. WHEN the backend receives a search request, THE Face_Matching_Service SHALL retrieve all face embeddings for photos in the selected album
3. FOR ALL face embeddings in the album, THE Face_Matching_Service SHALL calculate cosine similarity with the client embedding
4. WHEN calculating similarity, THE Face_Matching_Service SHALL filter results using the match threshold (similarity > 0.6)
5. THE Face_Matching_Service SHALL return matched photos sorted by similarity score in descending order
6. THE System SHALL complete the search and return results within 10 seconds for albums with up to 1000 photos

### Requirement 5: Cosine Similarity Calculation

**User Story:** As a system operator, I want accurate face matching using cosine similarity, so that clients receive relevant photo matches.

#### Acceptance Criteria

1. WHEN calculating cosine similarity, THE Face_Matching_Service SHALL compute the dot product of the two embedding vectors
2. WHEN calculating cosine similarity, THE Face_Matching_Service SHALL compute the magnitude of each embedding vector
3. THE Face_Matching_Service SHALL calculate similarity as: dot_product / (magnitude1 × magnitude2)
4. IF either embedding vector has zero magnitude, THEN THE Face_Matching_Service SHALL return similarity of 0
5. IF embedding vectors have different dimensions, THEN THE Face_Matching_Service SHALL throw an InvalidArgumentException
6. THE Face_Matching_Service SHALL return similarity values in the range [-1, 1]

### Requirement 6: Search Results Display

**User Story:** As a client, I want to see all photos that match my face with their similarity scores, so that I can decide which photos to purchase.

#### Acceptance Criteria

1. WHEN search results are received, THE System SHALL display matched photos in a grid layout
2. FOR ALL matched photos, THE System SHALL display the watermarked image, similarity percentage, and price
3. WHEN displaying results, THE System SHALL show photos ordered by similarity score (highest first)
4. IF no photos match the threshold, THEN THE System SHALL display a "no matches found" message
5. FOR ALL displayed photos, THE System SHALL provide an "Add to Cart" button
6. WHEN a client clicks "Add to Cart", THE System SHALL add the photo to the shopping cart

### Requirement 7: Input Validation

**User Story:** As a system operator, I want all inputs to be validated, so that the system remains secure and stable.

#### Acceptance Criteria

1. WHEN receiving a search request, THE System SHALL validate that embedding_vector is an array of exactly 128 elements
2. WHEN receiving a search request, THE System SHALL validate that all embedding vector elements are numeric values
3. WHEN receiving a search request, THE System SHALL validate that album_id exists in the database
4. IF validation fails, THEN THE System SHALL return a 422 Unprocessable Entity response with error details
5. THE System SHALL require user authentication before allowing access to face scan features

### Requirement 8: Privacy and Security

**User Story:** As a client, I want my face data to be handled securely and privately, so that my biometric information is protected.

#### Acceptance Criteria

1. THE System SHALL transmit all face embedding data over HTTPS connections only
2. THE System SHALL NOT store client face embeddings in the database after search completion
3. THE System SHALL only use client face embeddings for the immediate search operation
4. THE System SHALL require user authentication before allowing face scan access
5. THE System SHALL implement rate limiting to prevent abuse (maximum 10 searches per minute per user)

### Requirement 9: Performance and Scalability

**User Story:** As a system operator, I want the face matching to perform efficiently, so that clients receive fast search results.

#### Acceptance Criteria

1. WHEN querying photos, THE System SHALL use database indexes on album_id for efficient retrieval
2. THE System SHALL cache the list of available albums to reduce database queries
3. IF search results exceed 50 photos, THEN THE System SHALL implement pagination
4. THE Face_Matching_Service SHALL process similarity calculations for up to 1000 face embeddings within 10 seconds
5. THE System SHALL handle concurrent search requests from multiple users without performance degradation

### Requirement 10: Error Handling

**User Story:** As a client, I want clear error messages when something goes wrong, so that I know how to proceed.

#### Acceptance Criteria

1. IF face detection fails, THEN THE System SHALL display "Face not detected. Please try again with a clearer photo"
2. IF the camera cannot be accessed, THEN THE System SHALL display "Camera access denied. Please check permissions or upload a photo instead"
3. IF the search request fails, THEN THE System SHALL display "Search failed. Please try again"
4. IF the backend is unavailable, THEN THE System SHALL display "Service temporarily unavailable. Please try again later"
5. FOR ALL error conditions, THE System SHALL log error details for debugging purposes

