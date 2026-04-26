# Tasks: Landing Page & Face Scan Registration

## Phase 1: Database & Models

### 1. Create User Face Embeddings Table
- [ ] 1.1 Create migration for `user_face_embeddings` table
  - [ ] 1.1.1 Add columns: id, user_id, embedding_vector (TEXT), timestamps
  - [ ] 1.1.2 Add foreign key constraint on user_id (cascade delete)
  - [ ] 1.1.3 Add index on user_id
- [ ] 1.2 Create `UserFaceEmbedding` model
  - [ ] 1.2.1 Define fillable fields
  - [ ] 1.2.2 Add relationship to User model (belongsTo)
  - [ ] 1.2.3 Add accessor for decrypting embedding_vector
  - [ ] 1.2.4 Add mutator for encrypting embedding_vector

### 2. Update Users Table
- [ ] 2.1 Create migration to add `face_embedding_id` to users table
  - [ ] 2.1.1 Add nullable integer column `face_embedding_id`
  - [ ] 2.1.2 Add foreign key constraint to user_face_embeddings.id
- [ ] 2.2 Update User model
  - [ ] 2.2.1 Add `face_embedding_id` to fillable fields
  - [ ] 2.2.2 Add relationship to UserFaceEmbedding (hasOne)

## Phase 2: Backend - Multi-Step Registration

### 3. Create Multi-Step Registration Controller
- [ ] 3.1 Create `MultiStepRegistrationController`
  - [ ] 3.1.1 Implement `showStepOne()` method (display step 1 form)
  - [ ] 3.1.2 Implement `storeStepOne()` method (validate and store in session)
  - [ ] 3.1.3 Implement `showStepTwo()` method (display face scan page)
  - [ ] 3.1.4 Implement `storeStepTwo()` method (complete registration)

### 4. Implement Step One Logic
- [ ] 4.1 Create validation rules for step one
  - [ ] 4.1.1 Validate name (required, string, max:255)
  - [ ] 4.1.2 Validate email (required, email, unique:users)
  - [ ] 4.1.3 Validate password (required, confirmed, min:8)
  - [ ] 4.1.4 Validate role (required, in:customer,photographer)
- [ ] 4.2 Implement session token generation
  - [ ] 4.2.1 Generate 32-byte random token using Str::random()
  - [ ] 4.2.2 Store session data in Cache with 15-minute TTL
  - [ ] 4.2.3 Return JSON response with session_token
- [ ] 4.3 Add unit tests for step one
  - [ ] 4.3.1 Test valid input submission
  - [ ] 4.3.2 Test duplicate email rejection
  - [ ] 4.3.3 Test password validation
  - [ ] 4.3.4 Test session token generation

### 5. Implement Step Two Logic
- [ ] 5.1 Create validation rules for step two
  - [ ] 5.1.1 Validate session_token (required, string)
  - [ ] 5.1.2 Validate face_embedding (required, array, size:128)
  - [ ] 5.1.3 Validate face_embedding.* (numeric)
- [ ] 5.2 Implement session validation
  - [ ] 5.2.1 Retrieve session data from cache
  - [ ] 5.2.2 Check if session exists and not expired
  - [ ] 5.2.3 Return 419 error if session expired
- [ ] 5.3 Implement user creation with transaction
  - [ ] 5.3.1 Create User record with session data
  - [ ] 5.3.2 Encrypt face embedding using Crypt::encryptString()
  - [ ] 5.3.3 Create UserFaceEmbedding record
  - [ ] 5.3.4 Update User with face_embedding_id
  - [ ] 5.3.5 Rollback on any error
- [ ] 5.4 Implement post-registration actions
  - [ ] 5.4.1 Authenticate user (Auth::login)
  - [ ] 5.4.2 Invalidate session token (Cache::forget)
  - [ ] 5.4.3 Redirect to dashboard
- [ ] 5.5 Add unit tests for step two
  - [ ] 5.5.1 Test valid registration completion
  - [ ] 5.5.2 Test expired session handling
  - [ ] 5.5.3 Test invalid embedding dimensions
  - [ ] 5.5.4 Test transaction rollback on error

### 6. Update Routes
- [ ] 6.1 Add routes for multi-step registration
  - [ ] 6.1.1 GET /register → showStepOne
  - [ ] 6.1.2 POST /register/step-one → storeStepOne
  - [ ] 6.1.3 GET /register/step-two → showStepTwo (with session token)
  - [ ] 6.1.4 POST /register/step-two → storeStepTwo
- [ ] 6.2 Update root route
  - [ ] 6.2.1 Change GET / to display landing page (not redirect)
- [ ] 6.3 Add rate limiting middleware
  - [ ] 6.3.1 Apply throttle:5,60 to registration routes

## Phase 3: Frontend - Landing Page

### 7. Create Landing Page View
- [ ] 7.1 Create `resources/views/landing.blade.php`
  - [ ] 7.1.1 Create hero section with headline and CTA buttons
  - [ ] 7.1.2 Create features section (3+ key features)
  - [ ] 7.1.3 Create "How It Works" section
  - [ ] 7.1.4 Create footer with links
- [ ] 7.2 Style landing page with Tailwind CSS
  - [ ] 7.2.1 Implement responsive design (mobile, tablet, desktop)
  - [ ] 7.2.2 Add gradient backgrounds and modern styling
  - [ ] 7.2.3 Add hover effects and transitions
  - [ ] 7.2.4 Optimize images and assets
- [ ] 7.3 Add Indonesian content
  - [ ] 7.3.1 Write feature descriptions in Indonesian
  - [ ] 7.3.2 Write CTA button text in Indonesian
  - [ ] 7.3.3 Write "How It Works" content in Indonesian

## Phase 4: Frontend - Multi-Step Registration

### 8. Create Step One View
- [ ] 8.1 Create `resources/views/auth/register-step-one.blade.php`
  - [ ] 8.1.1 Create form with name, email, password, password_confirmation, role fields
  - [ ] 8.1.2 Add step indicator (Step 1 of 2)
  - [ ] 8.1.3 Add validation error display
  - [ ] 8.1.4 Add submit button with loading state
- [ ] 8.2 Implement client-side validation
  - [ ] 8.2.1 Validate email format
  - [ ] 8.2.2 Validate password length (min 8)
  - [ ] 8.2.3 Validate password confirmation match
  - [ ] 8.2.4 Display inline error messages
- [ ] 8.3 Implement form submission
  - [ ] 8.3.1 Prevent default form submission
  - [ ] 8.3.2 Send POST request to /register/step-one
  - [ ] 8.3.3 Handle success response (store session token, redirect to step 2)
  - [ ] 8.3.4 Handle error response (display validation errors)

### 9. Create Step Two View
- [ ] 9.1 Create `resources/views/auth/register-step-two.blade.php`
  - [ ] 9.1.1 Create step indicator (Step 2 of 2)
  - [ ] 9.1.2 Add instructions for face scan (Indonesian)
  - [ ] 9.1.3 Add camera capture button
  - [ ] 9.1.4 Add file upload input
  - [ ] 9.1.5 Add video preview element
  - [ ] 9.1.6 Add canvas element for capture
  - [ ] 9.1.7 Add image preview element
  - [ ] 9.1.8 Add "Complete Registration" button (disabled by default)
  - [ ] 9.1.9 Add loading indicator
- [ ] 9.2 Include face-api.js library
  - [ ] 9.2.1 Add face-api.js script tag
  - [ ] 9.2.2 Ensure models are loaded from /public/models
  - [ ] 9.2.3 Add error handling for model loading failures

### 10. Implement Face Scan JavaScript Module
- [ ] 10.1 Create `public/js/registration-face-scan.js`
  - [ ] 10.1.1 Import/reuse face-api.js functions from existing face-scan.js
  - [ ] 10.1.2 Implement `initializeRegistrationFaceScan()` function
  - [ ] 10.1.3 Implement camera capture handler
  - [ ] 10.1.4 Implement file upload handler
  - [ ] 10.1.5 Implement face embedding extraction
  - [ ] 10.1.6 Implement registration completion submission
- [ ] 10.2 Implement camera capture functionality
  - [ ] 10.2.1 Request camera permission
  - [ ] 10.2.2 Display video stream
  - [ ] 10.2.3 Capture frame after 3 seconds
  - [ ] 10.2.4 Stop camera stream
  - [ ] 10.2.5 Extract face embedding from captured frame
  - [ ] 10.2.6 Enable submit button on success
- [ ] 10.3 Implement file upload functionality
  - [ ] 10.3.1 Validate file type (JPEG, PNG, WebP)
  - [ ] 10.3.2 Validate file size (max 5MB)
  - [ ] 10.3.3 Display image preview
  - [ ] 10.3.4 Extract face embedding from uploaded image
  - [ ] 10.3.5 Enable submit button on success
- [ ] 10.4 Implement error handling
  - [ ] 10.4.1 Handle no face detected error
  - [ ] 10.4.2 Handle camera permission denied
  - [ ] 10.4.3 Handle invalid file type
  - [ ] 10.4.4 Handle file too large
  - [ ] 10.4.5 Display user-friendly error messages in Indonesian
- [ ] 10.5 Implement registration submission
  - [ ] 10.5.1 Get session token from URL parameter or hidden field
  - [ ] 10.5.2 Convert Float32Array embedding to regular array
  - [ ] 10.5.3 Send POST request to /register/step-two
  - [ ] 10.5.4 Handle success (redirect to dashboard)
  - [ ] 10.5.5 Handle session expired (redirect to step 1)
  - [ ] 10.5.6 Handle validation errors

## Phase 5: Testing

### 11. Backend Unit Tests
- [ ] 11.1 Test MultiStepRegistrationController
  - [ ] 11.1.1 Test storeStepOne with valid data
  - [ ] 11.1.2 Test storeStepOne with duplicate email
  - [ ] 11.1.3 Test storeStepOne with invalid password
  - [ ] 11.1.4 Test storeStepTwo with valid session and embedding
  - [ ] 11.1.5 Test storeStepTwo with expired session
  - [ ] 11.1.6 Test storeStepTwo with invalid embedding dimensions
- [ ] 11.2 Test UserFaceEmbedding model
  - [ ] 11.2.1 Test encryption/decryption of embedding_vector
  - [ ] 11.2.2 Test relationship to User model
- [ ] 11.3 Test session token generation
  - [ ] 11.3.1 Test token uniqueness
  - [ ] 11.3.2 Test token expiration

### 12. Frontend Unit Tests
- [ ] 12.1 Test registration-face-scan.js
  - [ ] 12.1.1 Test face embedding extraction with mock face-api.js
  - [ ] 12.1.2 Test camera capture flow
  - [ ] 12.1.3 Test file upload flow
  - [ ] 12.1.4 Test error handling (no face detected)
  - [ ] 12.1.5 Test registration submission
- [ ] 12.2 Test form validation
  - [ ] 12.2.1 Test email validation
  - [ ] 12.2.2 Test password validation
  - [ ] 12.2.3 Test password confirmation validation

### 13. Integration Tests
- [ ] 13.1 Test complete registration flow
  - [ ] 13.1.1 Visit landing page
  - [ ] 13.1.2 Click register button
  - [ ] 13.1.3 Fill and submit step 1 form
  - [ ] 13.1.4 Upload face photo in step 2
  - [ ] 13.1.5 Complete registration
  - [ ] 13.1.6 Verify user created in database
  - [ ] 13.1.7 Verify face embedding stored and encrypted
  - [ ] 13.1.8 Verify user authenticated
  - [ ] 13.1.9 Verify redirect to dashboard
- [ ] 13.2 Test session expiration scenario
  - [ ] 13.2.1 Submit step 1
  - [ ] 13.2.2 Wait 16 minutes
  - [ ] 13.2.3 Submit step 2
  - [ ] 13.2.4 Verify 419 error returned
  - [ ] 13.2.5 Verify redirect to step 1

### 14. Property-Based Tests
- [ ] 14.1 Test embedding dimensionality invariant
  - [ ] 14.1.1 For all valid face images, embedding has 128 dimensions
- [ ] 14.2 Test session token uniqueness
  - [ ] 14.2.1 Generate 1000 tokens, verify all unique
- [ ] 14.3 Test encryption round-trip
  - [ ] 14.3.1 For all embeddings, encrypt then decrypt returns original
- [ ] 14.4 Test registration completeness
  - [ ] 14.4.1 For all registered customers, face_embedding_id is not null

## Phase 6: Security & Performance

### 15. Implement Security Measures
- [ ] 15.1 Add CSRF protection
  - [ ] 15.1.1 Verify CSRF token in all POST requests
  - [ ] 15.1.2 Include CSRF token in all forms
- [ ] 15.2 Add rate limiting
  - [ ] 15.2.1 Limit registration attempts to 5 per hour per IP
  - [ ] 15.2.2 Display clear error message when limit exceeded
- [ ] 15.3 Implement input sanitization
  - [ ] 15.3.1 Sanitize all user inputs
  - [ ] 15.3.2 Prevent XSS attacks
- [ ] 15.4 Secure session storage
  - [ ] 15.4.1 Configure Redis for session storage
  - [ ] 15.4.2 Enable Redis authentication
  - [ ] 15.4.3 Set proper TTL for sessions

### 16. Optimize Performance
- [ ] 16.1 Optimize landing page
  - [ ] 16.1.1 Compress images
  - [ ] 16.1.2 Lazy load non-critical assets
  - [ ] 16.1.3 Inline critical CSS
  - [ ] 16.1.4 Minify JavaScript and CSS
- [ ] 16.2 Optimize face detection
  - [ ] 16.2.1 Use Web Workers for face detection (if supported)
  - [ ] 16.2.2 Add timeout for face detection (30 seconds)
  - [ ] 16.2.3 Display loading indicator during detection
- [ ] 16.3 Optimize database queries
  - [ ] 16.3.1 Add indexes to frequently queried columns
  - [ ] 16.3.2 Use database transactions for atomicity

## Phase 7: Documentation & Deployment

### 17. Create Documentation
- [ ] 17.1 User documentation
  - [ ] 17.1.1 Write registration instructions (Indonesian)
  - [ ] 17.1.2 Write face scan tips (lighting, angle, distance)
  - [ ] 17.1.3 Create FAQ section
- [ ] 17.2 Developer documentation
  - [ ] 17.2.1 Document API endpoints
  - [ ] 17.2.2 Document database schema
  - [ ] 17.2.3 Document face embedding format
  - [ ] 17.2.4 Document security considerations
  - [ ] 17.2.5 Document deployment instructions

### 18. Prepare for Deployment
- [ ] 18.1 Environment configuration
  - [ ] 18.1.1 Configure Redis for production
  - [ ] 18.1.2 Enable HTTPS
  - [ ] 18.1.3 Set encryption keys
  - [ ] 18.1.4 Deploy face-api.js models to production
- [ ] 18.2 Database migration
  - [ ] 18.2.1 Test migrations on staging
  - [ ] 18.2.2 Create rollback plan
  - [ ] 18.2.3 Run migrations on production
- [ ] 18.3 Monitoring and logging
  - [ ] 18.3.1 Set up error logging
  - [ ] 18.3.2 Set up performance monitoring
  - [ ] 18.3.3 Set up security alerts

## Phase 8: Accessibility & Polish

### 19. Implement Accessibility Features
- [ ] 19.1 Add ARIA labels
  - [ ] 19.1.1 Add labels to form inputs
  - [ ] 19.1.2 Add labels to buttons
  - [ ] 19.1.3 Add labels to icons
- [ ] 19.2 Improve keyboard navigation
  - [ ] 19.2.1 Ensure all interactive elements are keyboard accessible
  - [ ] 19.2.2 Add focus indicators
  - [ ] 19.2.3 Test tab order
- [ ] 19.3 Add alternative text
  - [ ] 19.3.1 Add alt text to all images
  - [ ] 19.3.2 Mark decorative images appropriately
- [ ] 19.4 Ensure color contrast
  - [ ] 19.4.1 Test color contrast ratios (WCAG AA)
  - [ ] 19.4.2 Adjust colors if needed

### 20. Final Polish
- [ ] 20.1 UI/UX improvements
  - [ ] 20.1.1 Add smooth transitions between steps
  - [ ] 20.1.2 Add success animations
  - [ ] 20.1.3 Improve error message styling
  - [ ] 20.1.4 Add tooltips for help text
- [ ] 20.2 Cross-browser testing
  - [ ] 20.2.1 Test on Chrome
  - [ ] 20.2.2 Test on Firefox
  - [ ] 20.2.3 Test on Safari
  - [ ] 20.2.4 Test on Edge
  - [ ] 20.2.5 Test on mobile browsers
- [ ] 20.3 Final QA
  - [ ] 20.3.1 Test all happy paths
  - [ ] 20.3.2 Test all error paths
  - [ ] 20.3.3 Test edge cases
  - [ ] 20.3.4 Verify all requirements met
