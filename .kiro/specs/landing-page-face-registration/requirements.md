# Requirements Document: Landing Page & Face Scan Registration

## 1. Landing Page Requirements

### 1.1 Root URL Landing Page
**Priority**: High  
**Description**: The root URL (`/`) must display a professional landing page instead of redirecting to login.

**Acceptance Criteria**:
- Visiting `http://127.0.0.1:8000/` displays landing page
- Landing page does not redirect to `/login` or `/register`
- Page is accessible to unauthenticated users

### 1.2 Feature Showcase
**Priority**: High  
**Description**: Landing page must showcase platform features and benefits for photography event platform.

**Acceptance Criteria**:
- Display at least 3 key features of the platform
- Include visual elements (icons, images, or illustrations)
- Explain how the platform works for both photographers and customers
- Content is in Indonesian language

### 1.3 Call-to-Action Buttons
**Priority**: High  
**Description**: Landing page must provide clear navigation to registration and login.

**Acceptance Criteria**:
- "Register" button links to `/register`
- "Login" button links to `/login`
- Buttons are prominently displayed (hero section)
- Buttons are styled consistently with platform design

### 1.4 Responsive Design
**Priority**: Medium  
**Description**: Landing page must be responsive and work on all device sizes.

**Acceptance Criteria**:
- Page displays correctly on mobile (320px+)
- Page displays correctly on tablet (768px+)
- Page displays correctly on desktop (1024px+)
- Images and text scale appropriately

## 2. Multi-Step Registration Requirements

### 2.1 Registration Step 1: Basic Information
**Priority**: High  
**Description**: First step of registration collects basic user information.

**Acceptance Criteria**:
- Form includes fields: name, email, password, password confirmation, role
- Role selection offers "Customer" and "Photographer" options
- All fields are required and validated
- Email must be unique in database
- Password must be at least 8 characters
- Password confirmation must match password
- Validation errors displayed below respective fields

### 2.2 Session Token Generation
**Priority**: High  
**Description**: After step 1 submission, generate secure session token for step 2.

**Acceptance Criteria**:
- Session token is cryptographically secure (32 bytes)
- Token is unique for each registration attempt
- Session data stored in cache with 15-minute expiration
- Session data includes: name, email, hashed password, role, expiration timestamp

### 2.3 Registration Step 2: Face Scan (Mandatory)
**Priority**: High  
**Description**: Second step requires mandatory face scan for customer registration.

**Acceptance Criteria**:
- Step 2 page displays after successful step 1 submission
- Page clearly indicates face scan is mandatory
- User cannot skip or bypass face scan step
- Page provides two options: camera capture or file upload
- Instructions displayed in Indonesian

### 2.4 Camera Capture Option
**Priority**: High  
**Description**: Allow users to capture face photo using device camera.

**Acceptance Criteria**:
- "Capture with Camera" button requests camera permission
- Video stream displays in preview area
- Photo automatically captured after 3 seconds
- Camera stream stops after capture
- Face detection runs on captured image
- Success/error message displayed based on detection result

### 2.5 File Upload Option
**Priority**: High  
**Description**: Allow users to upload face photo from device storage.

**Acceptance Criteria**:
- File input accepts JPEG, PNG, WebP formats
- File size limit: 5MB maximum
- Uploaded image displays in preview area
- Face detection runs on uploaded image
- Invalid file types rejected with error message

### 2.6 Face Embedding Extraction
**Priority**: High  
**Description**: Extract 128-dimensional face embedding from captured/uploaded photo.

**Acceptance Criteria**:
- face-api.js models loaded before extraction
- Single face detected in image
- Face landmarks extracted
- 128-dimensional descriptor generated
- Embedding stored in memory only (not localStorage/sessionStorage)
- If no face detected: display error "Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas."
- If multiple faces: use first detected face or prompt user

### 2.7 Registration Completion
**Priority**: High  
**Description**: Complete registration after successful face scan.

**Acceptance Criteria**:
- "Complete Registration" button enabled only after face captured
- Button submits session token and face embedding to backend
- Backend validates session token (not expired)
- Backend validates embedding (128 dimensions, all numeric)
- User record created in database
- Face embedding encrypted and stored in `user_face_embeddings` table
- User automatically logged in after creation
- User redirected to role-based dashboard

### 2.8 Session Expiration Handling
**Priority**: Medium  
**Description**: Handle expired session tokens gracefully.

**Acceptance Criteria**:
- If session expired (>15 minutes): return 419 status
- Display message: "Session expired. Please start registration again."
- Redirect user back to step 1 (registration form)
- User must re-enter all information

## 3. Database Requirements

### 3.1 User Face Embeddings Table
**Priority**: High  
**Description**: Create new table to store user face embeddings.

**Acceptance Criteria**:
- Table name: `user_face_embeddings`
- Columns: `id`, `user_id`, `embedding_vector`, `created_at`, `updated_at`
- `user_id` is foreign key to `users.id` with cascade delete
- `embedding_vector` is TEXT column (stores encrypted JSON array)
- Index on `user_id` for fast lookups

### 3.2 Users Table Extension
**Priority**: High  
**Description**: Add face embedding reference to users table.

**Acceptance Criteria**:
- Add column: `face_embedding_id` (nullable integer)
- Foreign key to `user_face_embeddings.id`
- Nullable to support photographers (who don't need face scan)
- For customers: `face_embedding_id` must not be null after registration

## 4. Security Requirements

### 4.1 Face Embedding Encryption
**Priority**: High  
**Description**: All face embeddings must be encrypted at rest.

**Acceptance Criteria**:
- Use Laravel's `Crypt::encryptString()` for encryption
- Embeddings stored as encrypted JSON strings
- Decryption only when needed for search operations
- Raw embeddings never logged or exposed in API responses

### 4.2 Session Token Security
**Priority**: High  
**Description**: Session tokens must be cryptographically secure.

**Acceptance Criteria**:
- Tokens generated using `Str::random(32)` or equivalent
- Tokens stored in secure cache (Redis recommended)
- 15-minute TTL enforced
- Tokens invalidated immediately after use
- No token reuse allowed

### 4.3 Input Validation
**Priority**: High  
**Description**: All user inputs must be validated on both client and server.

**Acceptance Criteria**:
- Client-side validation for immediate feedback
- Server-side validation for security
- Email format validation
- Password strength validation (min 8 chars)
- Embedding dimension validation (exactly 128)
- All numeric values in embedding validated

### 4.4 CSRF Protection
**Priority**: High  
**Description**: All POST requests must include CSRF token.

**Acceptance Criteria**:
- CSRF token included in all registration forms
- Token verified on server side
- Laravel's CSRF middleware enabled
- Invalid tokens rejected with 419 status

### 4.5 Rate Limiting
**Priority**: Medium  
**Description**: Limit registration attempts to prevent abuse.

**Acceptance Criteria**:
- Maximum 5 registration attempts per IP per hour
- Rate limit applied to both step 1 and step 2
- Exceeded limit returns 429 status
- Clear error message displayed to user

## 5. Privacy Requirements

### 5.1 Client Embedding Non-Persistence
**Priority**: High  
**Description**: Client face embeddings must never be persisted during registration.

**Acceptance Criteria**:
- Embedding stored in JavaScript memory only
- No storage in localStorage, sessionStorage, or cookies
- Embedding sent to backend only once (during step 2 submission)
- Backend uses embedding immediately and does not log it
- After user creation, only encrypted embedding stored in database

### 5.2 User Face Embedding Storage
**Priority**: High  
**Description**: User face embeddings stored for future quick photo searches.

**Acceptance Criteria**:
- Embedding stored in `user_face_embeddings` table
- Embedding encrypted using Laravel's encryption
- Embedding linked to user record via `face_embedding_id`
- Embedding used for future face scan searches (find user's photos)

## 6. User Experience Requirements

### 6.1 Progress Indication
**Priority**: Medium  
**Description**: Show clear progress through registration steps.

**Acceptance Criteria**:
- Step indicator shows "Step 1 of 2" and "Step 2 of 2"
- Current step highlighted
- Completed steps marked with checkmark
- User cannot navigate back to step 1 after submission

### 6.2 Loading Indicators
**Priority**: Medium  
**Description**: Display loading states during async operations.

**Acceptance Criteria**:
- Loading spinner during step 1 submission
- Loading spinner during face detection
- Loading spinner during step 2 submission
- Buttons disabled during loading
- Clear status messages ("Processing...", "Detecting face...")

### 6.3 Error Messages
**Priority**: High  
**Description**: Display clear, user-friendly error messages in Indonesian.

**Acceptance Criteria**:
- Validation errors displayed below respective fields
- Face detection errors displayed prominently
- Session expiration message clear and actionable
- Network errors handled gracefully
- All messages in Indonesian language

### 6.4 Success Feedback
**Priority**: Medium  
**Description**: Provide positive feedback for successful actions.

**Acceptance Criteria**:
- Success message after step 1 submission
- Success message after face captured: "Wajah berhasil terdeteksi!"
- Visual checkmark or success icon
- Smooth transition between steps

## 7. Performance Requirements

### 7.1 Face Detection Performance
**Priority**: Medium  
**Description**: Face detection should complete within reasonable time.

**Acceptance Criteria**:
- Face detection completes within 5 seconds on average
- Loading indicator displayed during detection
- Timeout after 30 seconds with error message
- Use Web Workers if available to avoid blocking UI

### 7.2 Landing Page Load Time
**Priority**: Medium  
**Description**: Landing page should load quickly.

**Acceptance Criteria**:
- Initial page load under 3 seconds on 3G connection
- Images optimized and compressed
- Critical CSS inlined
- Non-critical assets lazy loaded

### 7.3 Session Storage Performance
**Priority**: Medium  
**Description**: Session operations should be fast.

**Acceptance Criteria**:
- Session token lookup under 100ms
- Use Redis for cache storage (recommended)
- Automatic cleanup of expired sessions

## 8. Compatibility Requirements

### 8.1 Browser Support
**Priority**: High  
**Description**: Support modern browsers with camera/file upload capabilities.

**Acceptance Criteria**:
- Chrome 90+ (desktop and mobile)
- Firefox 88+ (desktop and mobile)
- Safari 14+ (desktop and mobile)
- Edge 90+
- Graceful degradation for unsupported browsers

### 8.2 Camera API Support
**Priority**: High  
**Description**: Handle browsers without camera support.

**Acceptance Criteria**:
- Detect camera availability before showing capture option
- If camera not available: show only file upload option
- Clear message if camera permission denied
- Fallback to file upload always available

## 9. Testing Requirements

### 9.1 Unit Test Coverage
**Priority**: High  
**Description**: Comprehensive unit tests for all components.

**Acceptance Criteria**:
- Backend: Test all controller methods
- Backend: Test validation rules
- Backend: Test encryption/decryption
- Frontend: Test face embedding extraction
- Frontend: Test form submission
- Minimum 80% code coverage

### 9.2 Integration Tests
**Priority**: High  
**Description**: End-to-end tests for complete registration flow.

**Acceptance Criteria**:
- Test complete registration flow (step 1 → step 2 → dashboard)
- Test session expiration scenario
- Test invalid face photo scenario
- Test duplicate email scenario
- Test all error paths

### 9.3 Property-Based Tests
**Priority**: Medium  
**Description**: Property-based tests for invariants.

**Acceptance Criteria**:
- Test embedding dimensionality (always 128)
- Test session token uniqueness
- Test encryption round-trip
- Test registration completeness (all customers have embeddings)

## 10. Documentation Requirements

### 10.1 User Documentation
**Priority**: Medium  
**Description**: Provide clear instructions for users.

**Acceptance Criteria**:
- Registration instructions on landing page
- Face scan tips (lighting, angle, distance)
- FAQ section for common issues
- All documentation in Indonesian

### 10.2 Developer Documentation
**Priority**: Medium  
**Description**: Document technical implementation.

**Acceptance Criteria**:
- API endpoint documentation
- Database schema documentation
- Face embedding format documentation
- Security considerations documented
- Deployment instructions

## 11. Deployment Requirements

### 11.1 Environment Configuration
**Priority**: High  
**Description**: Proper environment setup for production.

**Acceptance Criteria**:
- Redis configured for session storage
- HTTPS enabled for secure transmission
- Encryption keys properly configured
- face-api.js models deployed to `/public/models`
- Environment variables documented

### 11.2 Database Migration
**Priority**: High  
**Description**: Safe migration of existing users.

**Acceptance Criteria**:
- Migration creates `user_face_embeddings` table
- Migration adds `face_embedding_id` to `users` table
- Existing users not affected (nullable column)
- Rollback migration available
- Migration tested on staging environment

## 12. Accessibility Requirements

### 12.1 WCAG Compliance
**Priority**: Medium  
**Description**: Landing page and registration forms should be accessible.

**Acceptance Criteria**:
- Proper heading hierarchy (h1, h2, h3)
- Form labels associated with inputs
- Error messages announced to screen readers
- Keyboard navigation supported
- Sufficient color contrast (WCAG AA)

### 12.2 Alternative Text
**Priority**: Medium  
**Description**: All images have descriptive alt text.

**Acceptance Criteria**:
- Landing page images have alt text
- Icons have aria-labels
- Decorative images marked as such
- Alt text in Indonesian
