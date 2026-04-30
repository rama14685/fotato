# Requirements Document: Face-Filtered Photo Display

## Introduction

Fitur **Face-Filtered Photo Display** mengintegrasikan pencarian album dengan face matching untuk menampilkan foto yang telah difilter berdasarkan wajah pembeli. Ketika pembeli memilih album dari hasil pencarian, sistem secara otomatis memanggil Face Matching Service untuk memfilter foto, menampilkan hanya foto yang mengandung wajah pembeli dengan badge similarity score, dan menyediakan integrasi dengan shopping cart. Fitur ini merupakan Spec 3 dari 3 dalam customer dashboard, menghubungkan album selection (Spec 2) dengan face matching algorithm (Spec 1) untuk memberikan pengalaman pencarian foto yang personal dan efisien.

## Glossary

- **Album_Detail_Page**: Halaman yang menampilkan foto-foto dalam album yang telah difilter berdasarkan wajah pembeli
- **Face_Matching_Service**: Service dari Spec 1 yang menyediakan cosine similarity calculation dan face matching logic
- **Customer_Face_Embedding**: Face embedding 128-dimensional yang tersimpan saat registrasi pembeli
- **Photo_Face_Embedding**: Face embedding yang tersimpan untuk setiap wajah dalam foto album
- **Filtered_Photo_Grid**: Grid responsif yang menampilkan foto-foto yang match dengan wajah pembeli
- **Similarity_Badge**: Badge yang menampilkan persentase kecocokan wajah pada setiap foto (contoh: "95% match")
- **Similarity_Threshold**: Nilai minimum similarity (default 0.6) untuk menganggap foto sebagai match
- **No_Match_Fallback**: Tampilan alternatif ketika tidak ada foto yang match dengan threshold
- **Shopping_Cart**: Keranjang belanja untuk menyimpan foto yang akan dibeli
- **Watermarked_Image**: Foto dengan watermark yang ditampilkan sebelum pembelian
- **Loading_Indicator**: Indikator visual yang menunjukkan proses face matching sedang berlangsung
- **Lazy_Loading**: Teknik loading gambar secara bertahap saat user scroll
- **Pagination**: Pembagian hasil foto menjadi beberapa halaman (25 foto per halaman)

## Requirements

### Requirement 1: Album Selection Handler

**User Story:** As a customer, I want to click an album card from search results and view photos in that album, so that I can find photos of myself.

#### Acceptance Criteria

1. WHEN a customer clicks an album card from the search results, THE System SHALL navigate to the album detail page with the album ID
2. WHEN the album detail page loads, THE System SHALL display the album title, event date, location, and photographer name
3. WHEN the album detail page loads, THE System SHALL display a loading indicator with message "Memfilter foto berdasarkan wajah Anda..."
4. THE System SHALL retrieve the customer's face embedding from the database using the authenticated customer ID
5. THE System SHALL retrieve all photos in the selected album with their associated face embeddings from the database
6. THE System SHALL use database indexes on album_id for efficient photo retrieval

### Requirement 2: Customer Face Embedding Retrieval

**User Story:** As a system operator, I want to retrieve and validate the customer's face embedding, so that face matching can be performed accurately.

#### Acceptance Criteria

1. WHEN retrieving the customer's face embedding, THE System SHALL query the user_face_embeddings table using the authenticated customer's user ID
2. IF the customer does not have a stored face embedding, THEN THE System SHALL redirect to the face registration page with message "Silakan lengkapi registrasi wajah Anda terlebih dahulu"
3. WHEN the face embedding is retrieved, THE System SHALL decrypt the stored embedding vector
4. THE System SHALL validate that the decrypted embedding is a 128-dimensional numeric array
5. IF the embedding validation fails, THEN THE System SHALL log an error and display "Terjadi kesalahan dengan data wajah Anda. Silakan hubungi administrator"
6. THE System SHALL cache the customer's decrypted embedding for the session duration to avoid repeated decryption

### Requirement 3: Photo and Face Embedding Retrieval

**User Story:** As a system operator, I want to efficiently retrieve all photos and their face embeddings from an album, so that face matching can be performed.

#### Acceptance Criteria

1. WHEN retrieving photos for an album, THE System SHALL query the photos table filtered by album_id
2. WHEN retrieving photos, THE System SHALL eager load the associated face_embeddings relationship to avoid N+1 queries
3. THE System SHALL retrieve photo metadata including id, file_path, price, and watermark_path
4. FOR ALL photos in the album, THE System SHALL retrieve all associated face embeddings (multiple faces per photo)
5. THE System SHALL decrypt all photo face embeddings before passing to the Face Matching Service
6. IF a photo has no face embeddings, THEN THE System SHALL exclude that photo from face matching (it will not appear in filtered results)

### Requirement 4: Face Matching Service Integration

**User Story:** As a developer, I want to call the Face Matching Service with customer and photo embeddings, so that I can identify photos containing the customer's face.

#### Acceptance Criteria

1. WHEN all embeddings are retrieved, THE System SHALL call the Face_Matching_Service with the customer embedding and collection of photo embeddings
2. THE System SHALL pass the configured similarity threshold (default 0.6) to the Face_Matching_Service
3. THE Face_Matching_Service SHALL return a collection of match results containing photo ID and similarity score
4. THE System SHALL filter photos where similarity score is greater than or equal to the threshold
5. THE System SHALL sort filtered photos by similarity score in descending order (highest match first)
6. IF the Face_Matching_Service throws an exception, THEN THE System SHALL catch the exception, log the error, and display "Terjadi kesalahan saat mencocokkan foto. Silakan coba lagi"

### Requirement 5: Filtered Photo Grid Display

**User Story:** As a customer, I want to see photos containing my face in a responsive grid layout, so that I can easily browse and select photos to purchase.

#### Acceptance Criteria

1. WHEN face matching completes successfully, THE System SHALL display filtered photos in a responsive grid layout
2. THE Filtered_Photo_Grid SHALL display 4 columns on desktop (≥1024px), 3 columns on tablet (768px-1023px), 2 columns on small tablet (640px-767px), and 1 column on mobile (<640px)
3. FOR ALL displayed photos, THE System SHALL show the watermarked image
4. FOR ALL displayed photos, THE System SHALL display the photo price in Indonesian Rupiah format (e.g., "Rp 50.000")
5. THE System SHALL implement lazy loading for photo images (load images as user scrolls)
6. THE System SHALL display a photo placeholder while images are loading
7. IF a photo image fails to load, THEN THE System SHALL display a placeholder with message "Gambar tidak dapat dimuat"

### Requirement 6: Similarity Badge Display

**User Story:** As a customer, I want to see how well each photo matches my face, so that I can prioritize photos with the best matches.

#### Acceptance Criteria

1. FOR ALL displayed photos, THE System SHALL show a similarity badge with the match percentage
2. THE Similarity_Badge SHALL display the percentage as an integer (e.g., "95% match", "87% match")
3. THE Similarity_Badge SHALL be positioned at the top-right corner of each photo card
4. THE Similarity_Badge SHALL use a semi-transparent background for visibility over the photo
5. THE Similarity_Badge SHALL use color coding: green for ≥80%, yellow for 60-79%, orange for <60%
6. THE System SHALL calculate the percentage as: (similarity_score × 100) rounded to nearest integer

### Requirement 7: No-Match Fallback Display

**User Story:** As a customer, I want to know when no photos match my face and have the option to view all photos, so that I understand why the album appears empty.

#### Acceptance Criteria

1. IF no photos in the album have similarity score ≥ threshold, THEN THE System SHALL display the no-match fallback view
2. THE no-match fallback SHALL display the message "Tidak ada foto yang cocok dengan wajah Anda di album ini"
3. THE no-match fallback SHALL display the current similarity threshold value (e.g., "Threshold: 60%")
4. THE no-match fallback SHALL display a "Lihat Semua Foto" button
5. WHEN the customer clicks "Lihat Semua Foto", THE System SHALL display all photos in the album without face filtering
6. WHEN displaying all photos (no filter), THE System SHALL NOT display similarity badges
7. THE System SHALL log no-match events with album ID, customer ID, and photo count for analytics

### Requirement 8: Shopping Cart Integration - Add to Cart

**User Story:** As a customer, I want to add filtered photos to my shopping cart, so that I can purchase photos of myself.

#### Acceptance Criteria

1. FOR ALL displayed photos, THE System SHALL display a "Tambah ke Keranjang" button
2. WHEN a customer clicks "Tambah ke Keranjang", THE System SHALL add the photo to the customer's shopping cart in the database
3. THE System SHALL prevent duplicate photos in the cart (if photo already exists, display "Foto sudah ada di keranjang")
4. WHEN a photo is successfully added to cart, THE System SHALL display a success notification "Foto berhasil ditambahkan ke keranjang"
5. THE System SHALL update the cart item count in the header/navigation bar
6. THE "Tambah ke Keranjang" button SHALL be disabled and show "Sudah di Keranjang" for photos already in the cart

### Requirement 9: Shopping Cart Integration - Cart Icon and Badge

**User Story:** As a customer, I want to see how many items are in my cart, so that I can track my selections.

#### Acceptance Criteria

1. THE System SHALL display a shopping cart icon in the page header/navigation
2. THE cart icon SHALL display a badge with the current number of items in the cart
3. WHEN a photo is added to the cart, THE System SHALL update the badge count immediately without page reload
4. WHEN the customer clicks the cart icon, THE System SHALL navigate to the shopping cart page
5. THE badge count SHALL be visible and readable (minimum 16px font size, contrasting color)

### Requirement 10: Performance - Face Matching Execution Time

**User Story:** As a customer, I want photo filtering to complete quickly, so that I don't have to wait long to see my photos.

#### Acceptance Criteria

1. THE System SHALL complete face matching for albums with up to 1000 photos within 10 seconds
2. THE System SHALL complete face matching for albums with up to 500 photos within 5 seconds
3. THE System SHALL complete face matching for albums with up to 100 photos within 2 seconds
4. IF face matching takes longer than 10 seconds, THEN THE System SHALL log a performance warning with album ID and photo count
5. THE System SHALL display the loading indicator throughout the entire face matching process

### Requirement 11: Performance - Pagination

**User Story:** As a customer, I want photos to load quickly even in large albums, so that I can start browsing immediately.

#### Acceptance Criteria

1. THE System SHALL implement pagination for filtered results exceeding 25 photos
2. THE System SHALL display 25 photos per page
3. THE System SHALL display pagination controls (Previous, Page Numbers, Next) at the bottom of the photo grid
4. WHEN a customer clicks a page number, THE System SHALL load and display photos for that page
5. THE System SHALL maintain the current page number in the URL query parameter (e.g., ?page=2)
6. THE System SHALL scroll to the top of the photo grid when changing pages

### Requirement 12: Performance - Lazy Loading

**User Story:** As a customer, I want images to load efficiently, so that the page loads quickly and uses less bandwidth.

#### Acceptance Criteria

1. THE System SHALL implement lazy loading for photo images using the Intersection Observer API or equivalent
2. THE System SHALL load images only when they are about to enter the viewport (within 200px margin)
3. THE System SHALL display a placeholder or skeleton loader while images are loading
4. THE System SHALL prioritize loading images in the current viewport first
5. THE System SHALL load watermarked images (not full-resolution originals)

### Requirement 13: Performance - Session Caching

**User Story:** As a system operator, I want to cache customer embeddings and album metadata, so that subsequent operations are faster.

#### Acceptance Criteria

1. THE System SHALL cache the customer's decrypted face embedding in the session after first retrieval
2. THE System SHALL cache album metadata (title, date, location, photographer) for the session duration
3. THE System SHALL NOT cache photo face embeddings (too large for session storage)
4. THE System SHALL invalidate the customer embedding cache when the session expires
5. THE System SHALL use the cached embedding for subsequent face matching operations in the same session

### Requirement 14: Error Handling - Missing Customer Embedding

**User Story:** As a customer, I want clear guidance when my face data is missing, so that I know how to proceed.

#### Acceptance Criteria

1. IF the customer does not have a stored face embedding, THEN THE System SHALL redirect to the face registration page
2. THE System SHALL display the message "Silakan lengkapi registrasi wajah Anda terlebih dahulu untuk menggunakan fitur ini"
3. THE System SHALL provide a "Daftar Wajah Sekarang" button that navigates to the registration page
4. THE System SHALL log the missing embedding event with customer ID for analytics
5. THE System SHALL NOT attempt face matching when the customer embedding is missing

### Requirement 15: Error Handling - Face Matching Service Errors

**User Story:** As a customer, I want to know when face matching fails, so that I can retry or contact support.

#### Acceptance Criteria

1. IF the Face_Matching_Service throws an InvalidArgumentException, THEN THE System SHALL log the error details and display "Terjadi kesalahan dengan data wajah. Silakan hubungi administrator"
2. IF the Face_Matching_Service throws any other exception, THEN THE System SHALL log the error and display "Terjadi kesalahan saat mencocokkan foto. Silakan coba lagi"
3. THE error message SHALL include a "Coba Lagi" button that reloads the album detail page
4. THE System SHALL log all face matching errors with customer ID, album ID, error type, and error message
5. THE System SHALL NOT expose technical error details to the customer

### Requirement 16: Error Handling - Photo Loading Errors

**User Story:** As a customer, I want to see which photos failed to load, so that I can retry or skip them.

#### Acceptance Criteria

1. IF a photo image fails to load (404, 500, network error), THEN THE System SHALL display a placeholder image with message "Gambar tidak dapat dimuat"
2. THE placeholder SHALL include a "Coba Lagi" button that attempts to reload the image
3. THE System SHALL NOT remove photos from the grid when images fail to load
4. THE System SHALL log image loading errors with photo ID and error type
5. THE "Tambah ke Keranjang" button SHALL remain functional even if the image fails to load

### Requirement 17: Error Handling - Album Not Found

**User Story:** As a customer, I want to know when an album doesn't exist, so that I understand why I can't view it.

#### Acceptance Criteria

1. IF the album ID in the URL does not exist in the database, THEN THE System SHALL display a 404 error page
2. THE 404 page SHALL display the message "Album tidak ditemukan"
3. THE 404 page SHALL provide a "Kembali ke Pencarian" button that navigates to the dashboard search page
4. THE System SHALL log album not found events with album ID and customer ID
5. THE System SHALL return HTTP status code 404

### Requirement 18: Mobile Responsiveness - Layout

**User Story:** As a customer, I want to use the photo display on my mobile device, so that I can browse photos on the go.

#### Acceptance Criteria

1. THE Album_Detail_Page SHALL display correctly on mobile devices with minimum width 320px
2. THE Filtered_Photo_Grid SHALL use a single-column layout on mobile devices (<640px)
3. THE album metadata (title, date, location) SHALL stack vertically on mobile devices
4. THE pagination controls SHALL be touch-friendly with minimum 44px touch target size
5. THE "Tambah ke Keranjang" button SHALL be touch-friendly with minimum 44px height

### Requirement 19: Mobile Responsiveness - Image Optimization

**User Story:** As a customer, I want images to load quickly on mobile networks, so that I can browse photos without long waits.

#### Acceptance Criteria

1. THE System SHALL serve appropriately sized images based on device screen width
2. THE System SHALL serve images at 2x resolution for high-DPI mobile screens
3. THE System SHALL use progressive JPEG or WebP format for faster perceived loading
4. THE System SHALL implement lazy loading with higher priority for mobile devices
5. THE System SHALL display image file size and loading progress on slow connections

### Requirement 20: Accessibility - Semantic HTML

**User Story:** As a customer with accessibility needs, I want the page to use semantic HTML, so that I can navigate with assistive technologies.

#### Acceptance Criteria

1. THE Album_Detail_Page SHALL use semantic HTML elements (main, section, article, nav)
2. THE Filtered_Photo_Grid SHALL use appropriate list elements (ul, li) for the photo grid
3. THE pagination controls SHALL use nav element with aria-label="Pagination"
4. THE "Tambah ke Keranjang" buttons SHALL use button elements (not div or span)
5. THE loading indicator SHALL use appropriate ARIA attributes (aria-live="polite", role="status")

### Requirement 21: Accessibility - Image Alt Text

**User Story:** As a customer with visual impairments, I want descriptive alt text for images, so that I can understand the content.

#### Acceptance Criteria

1. FOR ALL photo images, THE System SHALL provide alt text in format "Foto dari album {album_title} dengan kecocokan {similarity_percentage}%"
2. THE placeholder images SHALL have alt text "Gambar sedang dimuat" or "Gambar tidak dapat dimuat"
3. THE watermark images SHALL have empty alt text (alt="") as they are decorative
4. THE album thumbnail SHALL have alt text describing the album
5. THE System SHALL NOT use generic alt text like "image" or "photo"

### Requirement 22: Accessibility - Keyboard Navigation

**User Story:** As a customer who uses keyboard navigation, I want to navigate the page with keyboard, so that I can use the feature without a mouse.

#### Acceptance Criteria

1. THE System SHALL support Tab key navigation through all interactive elements (buttons, links, pagination)
2. THE System SHALL display visible focus indicators for all focusable elements
3. THE "Tambah ke Keranjang" buttons SHALL be activatable with Enter or Space key
4. THE pagination controls SHALL be navigable with Tab and activatable with Enter
5. THE System SHALL support Escape key to close any modal dialogs or notifications

### Requirement 23: Accessibility - Color Contrast

**User Story:** As a customer with visual impairments, I want sufficient color contrast, so that I can read text and see buttons clearly.

#### Acceptance Criteria

1. THE System SHALL ensure text has minimum 4.5:1 contrast ratio with background (WCAG AA standard)
2. THE Similarity_Badge text SHALL have minimum 4.5:1 contrast ratio with badge background
3. THE "Tambah ke Keranjang" button SHALL have minimum 3:1 contrast ratio with surrounding content
4. THE pagination controls SHALL have sufficient contrast in all states (default, hover, active, disabled)
5. THE System SHALL NOT rely solely on color to convey information (use text labels in addition to color coding)

### Requirement 24: Accessibility - Screen Reader Support

**User Story:** As a customer using a screen reader, I want proper ARIA labels and announcements, so that I can understand the page content and interactions.

#### Acceptance Criteria

1. THE loading indicator SHALL announce "Memfilter foto berdasarkan wajah Anda" to screen readers
2. WHEN a photo is added to cart, THE System SHALL announce "Foto berhasil ditambahkan ke keranjang" to screen readers
3. THE cart badge SHALL have aria-label="Keranjang belanja, {count} item"
4. THE Similarity_Badge SHALL have aria-label="{similarity_percentage} persen kecocokan"
5. THE pagination controls SHALL have aria-label indicating current page and total pages

### Requirement 25: Analytics and Monitoring - User Behavior

**User Story:** As a product manager, I want to track how customers use the photo display feature, so that I can optimize the user experience.

#### Acceptance Criteria

1. THE System SHALL log when a customer views an album detail page with album ID and customer ID
2. THE System SHALL log the number of filtered photos displayed for each album view
3. THE System SHALL log when a customer clicks "Lihat Semua Foto" (no-match fallback)
4. THE System SHALL log when a customer adds a photo to cart with photo ID, similarity score, and album ID
5. THE System SHALL log pagination interactions (page number clicked)

### Requirement 26: Analytics and Monitoring - Performance Metrics

**User Story:** As a system operator, I want to monitor face matching performance, so that I can identify and resolve performance issues.

#### Acceptance Criteria

1. THE System SHALL log face matching execution time for each album view
2. THE System SHALL log the number of photos processed during face matching
3. THE System SHALL log the number of photos that matched the threshold
4. IF face matching takes longer than 10 seconds, THEN THE System SHALL log a performance warning
5. THE System SHALL generate daily reports on average face matching time by album size

### Requirement 27: Analytics and Monitoring - Error Tracking

**User Story:** As a system operator, I want to track errors and failures, so that I can improve system reliability.

#### Acceptance Criteria

1. THE System SHALL log all face matching errors with error type, customer ID, album ID, and timestamp
2. THE System SHALL log all image loading errors with photo ID and error type
3. THE System SHALL log instances where customer face embedding is missing
4. THE System SHALL log instances where no photos match the threshold (no-match events)
5. THE System SHALL generate daily error reports with error counts by type

### Requirement 28: Security - Authentication and Authorization

**User Story:** As a system operator, I want to ensure only authenticated customers can view filtered photos, so that the system is secure.

#### Acceptance Criteria

1. THE System SHALL require customer authentication before allowing access to the album detail page
2. THE System SHALL verify the authenticated customer's identity before retrieving their face embedding
3. THE System SHALL use the authenticated customer's user ID for all database queries (no user ID in URL parameters)
4. THE System SHALL implement CSRF protection for all "Add to Cart" requests
5. THE System SHALL use HTTPS for all requests to protect face embedding data in transit

### Requirement 29: Security - Rate Limiting

**User Story:** As a system operator, I want to prevent abuse of the face matching feature, so that the system remains available for legitimate users.

#### Acceptance Criteria

1. THE System SHALL implement rate limiting of 20 album views per minute per authenticated customer
2. IF a customer exceeds the rate limit, THEN THE System SHALL return HTTP 429 Too Many Requests
3. THE rate limit error page SHALL display "Terlalu banyak permintaan. Silakan tunggu beberapa saat"
4. THE System SHALL log rate limit violations with customer ID and timestamp
5. THE System SHALL use Redis or similar for distributed rate limiting across multiple servers

### Requirement 30: Security - Data Privacy

**User Story:** As a customer, I want my face data to be handled securely, so that my biometric information remains private.

#### Acceptance Criteria

1. THE System SHALL NOT expose raw face embeddings in API responses, HTML source, or browser console
2. THE System SHALL NOT log raw face embedding values (log only metadata like dimension count)
3. THE System SHALL transmit all face embedding data over HTTPS connections only
4. THE System SHALL use encrypted database connections for retrieving face embeddings
5. THE System SHALL implement proper session management to prevent session hijacking

### Requirement 31: Testing - Unit Tests

**User Story:** As a developer, I want comprehensive unit tests, so that I can ensure individual components work correctly.

#### Acceptance Criteria

1. THE System SHALL have unit tests for the album detail controller with various input scenarios
2. THE System SHALL have unit tests for customer embedding retrieval with valid and invalid customer IDs
3. THE System SHALL have unit tests for photo retrieval with various album IDs
4. THE System SHALL have unit tests for the "Add to Cart" functionality with duplicate detection
5. THE System SHALL achieve minimum 80% code coverage for the album detail controller and related services

### Requirement 32: Testing - Integration Tests

**User Story:** As a developer, I want integration tests for the complete flow, so that I can ensure all components work together correctly.

#### Acceptance Criteria

1. THE System SHALL have integration tests for the complete flow: album selection → face matching → photo display
2. THE System SHALL have integration tests for the no-match fallback scenario
3. THE System SHALL have integration tests for adding photos to cart
4. THE System SHALL have integration tests for pagination functionality
5. THE System SHALL have integration tests for error scenarios (missing embedding, invalid album ID, face matching errors)

### Requirement 33: Testing - Property-Based Tests for Filtering

**User Story:** As a developer, I want property-based tests to verify filtering logic, so that I can ensure correctness for all possible inputs.

#### Acceptance Criteria

1. THE System SHALL have property-based tests verifying that all displayed photos have similarity score ≥ threshold
2. THE System SHALL have property-based tests verifying that photos are sorted by similarity score in descending order
3. THE System SHALL have property-based tests verifying that similarity percentages are calculated correctly (score × 100)
4. THE System SHALL have property-based tests verifying that no duplicate photos appear in the filtered results
5. THE System SHALL run property-based tests with at least 100 random test cases

### Requirement 34: Testing - Performance Tests

**User Story:** As a developer, I want performance tests to ensure face matching completes within acceptable time limits, so that I can detect performance regressions.

#### Acceptance Criteria

1. THE System SHALL have performance tests measuring face matching time for albums with 100, 500, and 1000 photos
2. THE System SHALL fail performance tests if face matching exceeds 10 seconds for 1000 photos
3. THE System SHALL have performance tests measuring page load time for the album detail page
4. THE System SHALL have performance tests measuring image lazy loading performance
5. THE System SHALL log performance test results for tracking over time

### Requirement 35: Testing - End-to-End Tests

**User Story:** As a developer, I want end-to-end tests simulating real user interactions, so that I can ensure the feature works correctly from the user's perspective.

#### Acceptance Criteria

1. THE System SHALL have end-to-end tests simulating: search for album → click album → view filtered photos → add photo to cart
2. THE System SHALL have end-to-end tests for the no-match fallback flow: view album → see no matches → click "Lihat Semua Foto"
3. THE System SHALL have end-to-end tests for pagination: view album → navigate to page 2 → navigate back to page 1
4. THE System SHALL have end-to-end tests for mobile viewport sizes
5. THE System SHALL have end-to-end tests for error scenarios with appropriate error messages displayed
