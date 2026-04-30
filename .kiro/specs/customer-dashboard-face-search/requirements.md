# Requirements Document: Customer Dashboard with Face-Based Photo Search

## Introduction

Fitur **Customer Dashboard with Face-Based Photo Search** menyederhanakan proses pencarian foto untuk pembeli dengan mengurangi input yang diperlukan dan secara otomatis memfilter foto berdasarkan pencocokan wajah. Pembeli hanya perlu memasukkan nama acara dan tanggal, kemudian sistem akan menampilkan album yang sesuai. Ketika pembeli memilih album, foto yang ditampilkan secara otomatis difilter untuk menampilkan hanya foto yang mengandung wajah pembeli berdasarkan face embedding yang tersimpan saat registrasi.

## Glossary

- **Customer_Dashboard**: Halaman utama pembeli untuk mencari dan menjelajahi foto event
- **Search_Form**: Form pencarian yang hanya meminta nama acara dan tanggal
- **Album_List**: Daftar album yang sesuai dengan kriteria pencarian
- **Face_Matching_Engine**: Service backend yang mencocokkan face embedding pembeli dengan foto dalam album
- **User_Face_Embedding**: Face embedding 128-dimensional yang tersimpan saat registrasi pembeli
- **Photo_Face_Embedding**: Face embedding yang tersimpan untuk setiap wajah dalam foto album
- **Similarity_Threshold**: Nilai minimum similarity (0.6) untuk menganggap foto sebagai match
- **Filtered_Photo_View**: Tampilan foto yang sudah difilter berdasarkan face matching
- **Album**: Koleksi foto dari satu event photography dengan metadata (nama, tanggal, lokasi)
- **Cosine_Similarity**: Metrik untuk mengukur kesamaan antara dua embedding vectors

## Requirements

### Requirement 1: Simplified Search Form

**User Story:** As a customer, I want to search for photos using only event name and date, so that I can quickly find relevant albums without filling many fields.

#### Acceptance Criteria

1. WHEN a customer accesses the dashboard, THE Customer_Dashboard SHALL display a search form with only two fields: event name and event date
2. THE Search_Form SHALL accept partial event name matches (case-insensitive search)
3. THE Search_Form SHALL accept date input in standard format (YYYY-MM-DD or date picker)
4. WHEN a customer submits the search form, THE System SHALL validate that at least one field is filled
5. THE Search_Form SHALL NOT require location, price range, or photographer name inputs

### Requirement 2: Album Search and Listing

**User Story:** As a customer, I want to see all albums matching my search criteria, so that I can choose which event to view photos from.

#### Acceptance Criteria

1. WHEN a customer submits a search, THE System SHALL query albums matching the event name and/or date
2. WHEN searching by event name, THE System SHALL perform case-insensitive partial matching on album title
3. WHEN searching by date, THE System SHALL match albums with exact event date
4. WHEN both fields are provided, THE System SHALL match albums satisfying both criteria (AND logic)
5. THE Album_List SHALL display album information including title, event date, location, photographer name, and photo count
6. THE Album_List SHALL order results by event date in descending order (most recent first)
7. IF no albums match the criteria, THEN THE System SHALL display "Tidak ada album yang sesuai dengan pencarian Anda"

### Requirement 3: Automatic Face-Based Photo Filtering

**User Story:** As a customer, I want to see only photos containing my face when I select an album, so that I don't have to manually search through all photos.

#### Acceptance Criteria

1. WHEN a customer selects an album, THE System SHALL retrieve the customer's stored face embedding from the database
2. WHEN the customer's face embedding is retrieved, THE Face_Matching_Engine SHALL compare it with all photo face embeddings in the selected album
3. FOR ALL photos in the album, THE Face_Matching_Engine SHALL calculate cosine similarity between the customer's embedding and each photo's face embeddings
4. THE Face_Matching_Engine SHALL filter photos where similarity score exceeds the similarity threshold (0.6)
5. THE Filtered_Photo_View SHALL display only photos matching the customer's face, sorted by similarity score in descending order
6. FOR ALL displayed photos, THE System SHALL show the watermarked image, similarity percentage, and price

### Requirement 4: Face Matching Algorithm

**User Story:** As a system operator, I want accurate face matching using cosine similarity, so that customers see relevant photos of themselves.

#### Acceptance Criteria

1. WHEN calculating cosine similarity, THE Face_Matching_Engine SHALL compute the dot product of the customer embedding and photo embedding vectors
2. WHEN calculating cosine similarity, THE Face_Matching_Engine SHALL compute the magnitude (L2 norm) of each embedding vector
3. THE Face_Matching_Engine SHALL calculate similarity as: dot_product / (magnitude_customer × magnitude_photo)
4. THE Face_Matching_Engine SHALL return similarity values in the range [-1, 1]
5. IF a photo contains multiple faces, THEN THE Face_Matching_Engine SHALL use the highest similarity score among all faces in that photo
6. IF either embedding vector has zero magnitude, THEN THE Face_Matching_Engine SHALL return similarity of 0
7. IF embedding vectors have different dimensions, THEN THE Face_Matching_Engine SHALL throw an InvalidArgumentException

### Requirement 5: Performance Optimization

**User Story:** As a customer, I want photo search results to load quickly, so that I can browse photos without waiting.

#### Acceptance Criteria

1. THE System SHALL complete face matching for albums with up to 1000 photos within 10 seconds
2. THE System SHALL use database indexes on album_id and user_id for efficient query performance
3. THE System SHALL implement pagination for filtered results exceeding 50 photos (25 photos per page)
4. THE System SHALL cache album metadata to reduce database queries
5. WHERE an album has more than 500 photos, THE System SHALL implement batch processing for face matching calculations

### Requirement 6: Fallback Behavior for No Matches

**User Story:** As a customer, I want to know when no photos match my face, so that I understand why the album appears empty.

#### Acceptance Criteria

1. IF no photos in the selected album exceed the similarity threshold, THEN THE System SHALL display "Tidak ada foto yang cocok dengan wajah Anda di album ini"
2. WHEN displaying the no-match message, THE System SHALL provide an option to "View All Photos in Album"
3. WHEN a customer clicks "View All Photos", THE System SHALL display all photos in the album without face filtering
4. THE System SHALL log instances of no matches for analytics purposes
5. THE System SHALL display the similarity threshold value in the no-match message for transparency

### Requirement 7: User Experience and Interface

**User Story:** As a customer, I want a clean and intuitive dashboard interface, so that I can easily navigate and find my photos.

#### Acceptance Criteria

1. THE Customer_Dashboard SHALL display a prominent search form at the top of the page
2. THE Customer_Dashboard SHALL use clear Indonesian labels for all form fields and buttons
3. WHEN displaying album results, THE System SHALL show album cards with thumbnail preview images
4. WHEN displaying filtered photos, THE System SHALL use a responsive grid layout (3-4 columns on desktop, 2 on tablet, 1 on mobile)
5. FOR ALL filtered photos, THE System SHALL display a similarity badge showing the match percentage
6. THE System SHALL provide an "Add to Cart" button for each displayed photo
7. THE System SHALL display a loading indicator during search and face matching operations

### Requirement 8: Customer Face Embedding Retrieval

**User Story:** As a system operator, I want to efficiently retrieve customer face embeddings, so that face matching can be performed quickly.

#### Acceptance Criteria

1. WHEN a customer logs in, THE System SHALL verify that the customer has a stored face embedding
2. IF a customer does not have a face embedding, THEN THE System SHALL redirect them to complete face registration
3. WHEN retrieving a customer's face embedding, THE System SHALL decrypt the stored embedding vector
4. THE System SHALL validate that the decrypted embedding is a 128-dimensional numeric array
5. THE System SHALL cache the customer's decrypted embedding for the duration of the session to avoid repeated decryption

### Requirement 9: Privacy and Security

**User Story:** As a customer, I want my face data to be handled securely, so that my biometric information remains private.

#### Acceptance Criteria

1. THE System SHALL require customer authentication before allowing access to the dashboard
2. THE System SHALL only retrieve and use the authenticated customer's own face embedding
3. THE System SHALL transmit all face embedding data over HTTPS connections only
4. THE System SHALL NOT expose raw face embeddings in API responses or browser console
5. THE System SHALL implement rate limiting to prevent abuse (maximum 20 album views per minute per customer)
6. THE System SHALL log all face matching operations for security audit purposes

### Requirement 10: Input Validation and Error Handling

**User Story:** As a customer, I want clear error messages when something goes wrong, so that I know how to proceed.

#### Acceptance Criteria

1. WHEN a customer submits an empty search form, THE System SHALL display "Mohon isi minimal satu field pencarian"
2. IF the customer's face embedding cannot be retrieved, THEN THE System SHALL display "Face embedding tidak ditemukan. Silakan lengkapi registrasi wajah Anda"
3. IF face matching fails due to server error, THEN THE System SHALL display "Terjadi kesalahan saat mencocokkan foto. Silakan coba lagi"
4. IF an album cannot be loaded, THEN THE System SHALL display "Album tidak dapat dimuat. Silakan coba lagi"
5. FOR ALL error conditions, THE System SHALL log error details with customer ID and timestamp for debugging

### Requirement 11: Shopping Cart Integration

**User Story:** As a customer, I want to add matched photos to my cart, so that I can purchase photos of myself.

#### Acceptance Criteria

1. WHEN a customer clicks "Add to Cart" on a filtered photo, THE System SHALL add the photo to the customer's shopping cart
2. THE System SHALL prevent duplicate photos in the cart (adding same photo twice)
3. WHEN a photo is added to cart, THE System SHALL display a success notification
4. THE System SHALL display a cart icon with item count in the dashboard header
5. THE System SHALL allow customers to view and manage their cart from the dashboard

### Requirement 12: Analytics and Monitoring

**User Story:** As a system operator, I want to track dashboard usage and face matching performance, so that I can optimize the system.

#### Acceptance Criteria

1. THE System SHALL log all search queries with event name, date, and result count
2. THE System SHALL log face matching operations with album ID, customer ID, match count, and processing time
3. THE System SHALL track instances where no photos match the customer's face
4. THE System SHALL monitor average face matching processing time per album size
5. THE System SHALL generate daily reports on dashboard usage and face matching success rates

### Requirement 13: Similarity Threshold Configuration

**User Story:** As a system administrator, I want to configure the face matching threshold, so that I can tune matching accuracy based on feedback.

#### Acceptance Criteria

1. THE System SHALL store the similarity threshold value in application configuration (default: 0.6)
2. THE System SHALL allow administrators to modify the threshold value without code changes
3. WHEN the threshold is changed, THE System SHALL apply the new value to all subsequent face matching operations
4. THE System SHALL validate that threshold values are between 0.0 and 1.0
5. THE System SHALL log threshold changes with administrator ID and timestamp

### Requirement 14: Mobile Responsiveness

**User Story:** As a customer, I want to use the dashboard on my mobile device, so that I can search for photos on the go.

#### Acceptance Criteria

1. THE Customer_Dashboard SHALL display correctly on mobile devices (320px minimum width)
2. THE Search_Form SHALL use mobile-friendly input controls (date picker, text input)
3. THE Album_List SHALL display as a single-column layout on mobile devices
4. THE Filtered_Photo_View SHALL display as a single-column grid on mobile devices
5. THE System SHALL optimize image loading for mobile networks (progressive loading, lazy loading)

### Requirement 15: Accessibility

**User Story:** As a customer with accessibility needs, I want the dashboard to be accessible, so that I can use it with assistive technologies.

#### Acceptance Criteria

1. THE Customer_Dashboard SHALL use semantic HTML elements (form, button, nav, main)
2. THE Search_Form SHALL have properly associated labels for all input fields
3. THE System SHALL provide alt text for all album thumbnail images
4. THE System SHALL ensure sufficient color contrast for text and buttons (WCAG AA standard)
5. THE System SHALL support keyboard navigation for all interactive elements

### Requirement 16: Browser Compatibility

**User Story:** As a customer, I want the dashboard to work on my preferred browser, so that I can access it without technical issues.

#### Acceptance Criteria

1. THE Customer_Dashboard SHALL function correctly on Chrome 90+ (desktop and mobile)
2. THE Customer_Dashboard SHALL function correctly on Firefox 88+ (desktop and mobile)
3. THE Customer_Dashboard SHALL function correctly on Safari 14+ (desktop and mobile)
4. THE Customer_Dashboard SHALL function correctly on Edge 90+
5. THE System SHALL display a warning message for unsupported browsers

### Requirement 17: Testing Requirements

**User Story:** As a developer, I want comprehensive tests for the dashboard feature, so that I can ensure reliability and catch regressions.

#### Acceptance Criteria

1. THE System SHALL have unit tests for the face matching algorithm with various embedding inputs
2. THE System SHALL have integration tests for the complete search-to-filtered-results flow
3. THE System SHALL have property-based tests verifying cosine similarity properties (symmetry, range, idempotence)
4. THE System SHALL have property-based tests verifying that filtered results always have similarity >= threshold
5. THE System SHALL have tests for all error handling scenarios (missing embedding, invalid album, no matches)
6. THE System SHALL achieve minimum 80% code coverage for dashboard-related code
