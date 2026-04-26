/**
 * Property-Based Test for Task 6.1: Search Request Payload Completeness
 * **Property 5: Search Request Payload Completeness**
 * **Validates: Requirements 4.1**
 * 
 * This test generates random search scenarios and verifies that all requests 
 * contain both embedding_vector and album_id fields as required by the API specification.
 */

import { jest } from '@jest/globals';
import fc from 'fast-check';

// Mock face-api.js
const mockFaceApi = {
    nets: {
        ssdMobilenetv1: { loadFromUri: jest.fn().mockResolvedValue() },
        faceLandmark68Net: { loadFromUri: jest.fn().mockResolvedValue() },
        faceRecognitionNet: { loadFromUri: jest.fn().mockResolvedValue() }
    },
    detectSingleFace: jest.fn()
};

// Mock global faceapi
global.faceapi = mockFaceApi;

// Mock fetch
global.fetch = jest.fn();

// Mock DOM methods
Object.defineProperty(window, 'alert', {
    writable: true,
    value: jest.fn()
});

// Import the module
const { 
    initializeSearchFunctionality,
    setFaceEmbedding
} = await import('./face-scan.js');

describe('Property-Based Test: Search Request Payload Completeness', () => {
    let mockSearchBtn, mockAlbumSelect, mockLoading, mockCsrfToken;
    let mockResultsContainer, mockResultsDiv;

    beforeEach(() => {
        // Reset all mocks
        jest.clearAllMocks();
        
        // Mock DOM elements for search functionality
        mockSearchBtn = {
            addEventListener: jest.fn(),
            disabled: false
        };
        
        mockAlbumSelect = {
            value: ''
        };
        
        mockLoading = {
            style: { display: 'none' }
        };
        
        mockCsrfToken = {
            getAttribute: jest.fn().mockReturnValue('test-csrf-token')
        };
        
        mockResultsContainer = {
            style: { display: 'none' },
            scrollIntoView: jest.fn()
        };
        
        mockResultsDiv = {
            innerHTML: '',
            appendChild: jest.fn()
        };

        // Mock document.getElementById
        document.getElementById = jest.fn((id) => {
            switch (id) {
                case 'searchBtn': return mockSearchBtn;
                case 'albumSelect': return mockAlbumSelect;
                case 'loading': return mockLoading;
                case 'resultsContainer': return mockResultsContainer;
                case 'results': return mockResultsDiv;
                default: return null;
            }
        });
        
        // Mock document.querySelector
        document.querySelector = jest.fn((selector) => {
            if (selector === 'meta[name="csrf-token"]') {
                return mockCsrfToken;
            }
            return null;
        });
        
        // Mock console methods
        console.log = jest.fn();
        console.error = jest.fn();
    });

    /**
     * **Property 5: Search Request Payload Completeness**
     * **Validates: Requirements 4.1**
     * 
     * For any search request sent to the backend, the payload SHALL contain 
     * both embedding_vector and album_id fields.
     */
    test('Property 5: All search requests contain both embedding_vector and album_id', async () => {
        await fc.assert(
            fc.asyncProperty(
                // Generate random album IDs (positive integers)
                fc.integer({ min: 1, max: 1000 }),
                // Generate random 128-dimensional embedding vectors with finite values only
                fc.array(
                    fc.float({ min: -1, max: 1, noNaN: true, noDefaultInfinity: true }), 
                    { minLength: 128, maxLength: 128 }
                ),
                async (albumId, embeddingVector) => {
                    // Normalize the embedding vector to handle floating-point precision issues
                    const normalizedEmbedding = embeddingVector.map(val => {
                        // Handle -0 and other edge cases
                        if (val === 0) return 0;
                        if (!isFinite(val)) return 0;
                        return val;
                    });
                    
                    // Setup test scenario
                    mockAlbumSelect.value = albumId.toString();
                    setFaceEmbedding(normalizedEmbedding);
                    
                    // Mock successful response
                    fetch.mockResolvedValueOnce({
                        ok: true,
                        json: jest.fn().mockResolvedValue({
                            success: true,
                            photos: []
                        })
                    });
                    
                    // Initialize search functionality
                    initializeSearchFunctionality();
                    
                    // Get the click handler
                    const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
                    
                    // Execute the search request
                    await clickHandler();
                    
                    // Verify that fetch was called
                    expect(fetch).toHaveBeenCalled();
                    
                    // Get the request details
                    const fetchCall = fetch.mock.calls[fetch.mock.calls.length - 1];
                    const [url, options] = fetchCall;
                    
                    // Verify URL is correct
                    expect(url).toBe('/face-scan/search');
                    
                    // Verify method is POST
                    expect(options.method).toBe('POST');
                    
                    // Verify headers contain required fields
                    expect(options.headers).toHaveProperty('Content-Type', 'application/json');
                    expect(options.headers).toHaveProperty('X-CSRF-TOKEN', 'test-csrf-token');
                    expect(options.headers).toHaveProperty('Accept', 'application/json');
                    
                    // Parse and verify request body
                    const requestBody = JSON.parse(options.body);
                    
                    // CRITICAL: Verify both required fields are present
                    expect(requestBody).toHaveProperty('embedding_vector');
                    expect(requestBody).toHaveProperty('album_id');
                    
                    // Verify embedding_vector is an array with correct structure
                    expect(Array.isArray(requestBody.embedding_vector)).toBe(true);
                    expect(requestBody.embedding_vector.length).toBe(128);
                    
                    // Verify all elements are finite numbers (no NaN, Infinity)
                    requestBody.embedding_vector.forEach((val, index) => {
                        expect(typeof val).toBe('number');
                        expect(isFinite(val)).toBe(true);
                    });
                    
                    // Verify album_id is correct type and value
                    expect(typeof requestBody.album_id).toBe('number');
                    expect(requestBody.album_id).toBe(albumId);
                    
                    // Verify no extra fields are present (payload completeness)
                    const expectedKeys = ['embedding_vector', 'album_id'];
                    const actualKeys = Object.keys(requestBody);
                    expect(actualKeys.sort()).toEqual(expectedKeys.sort());
                    
                    // Reset mocks for next iteration
                    fetch.mockClear();
                    mockSearchBtn.addEventListener.mockClear();
                }
            ),
            {
                numRuns: 50, // Run 50 random test cases
                timeout: 10000, // 10 second timeout per test
                verbose: false // Reduce noise in output
            }
        );
    });

    test('Property 5 Edge Cases: Payload completeness with boundary values', async () => {
        await fc.assert(
            fc.asyncProperty(
                // Test with edge case album IDs
                fc.oneof(
                    fc.constant(1), // Minimum valid album ID
                    fc.constant(999999), // Large album ID
                    fc.integer({ min: 1, max: 1000 })
                ),
                // Test with edge case embedding vectors (finite values only)
                fc.oneof(
                    // All zeros
                    fc.constant(new Array(128).fill(0)),
                    // All ones
                    fc.constant(new Array(128).fill(1)),
                    // All negative ones
                    fc.constant(new Array(128).fill(-1)),
                    // Mixed extreme values
                    fc.array(fc.oneof(fc.constant(-1), fc.constant(0), fc.constant(1)), { minLength: 128, maxLength: 128 }),
                    // Random valid values (no NaN, no Infinity)
                    fc.array(fc.float({ min: -1, max: 1, noNaN: true, noDefaultInfinity: true }), { minLength: 128, maxLength: 128 })
                ),
                async (albumId, embeddingVector) => {
                    // Normalize the embedding vector to handle edge cases
                    const normalizedEmbedding = embeddingVector.map(val => {
                        if (!isFinite(val)) return 0;
                        if (val === 0) return 0; // Handle -0
                        return val;
                    });
                    
                    // Setup test scenario
                    mockAlbumSelect.value = albumId.toString();
                    setFaceEmbedding(normalizedEmbedding);
                    
                    // Mock successful response
                    fetch.mockResolvedValueOnce({
                        ok: true,
                        json: jest.fn().mockResolvedValue({
                            success: true,
                            photos: []
                        })
                    });
                    
                    // Initialize search functionality
                    initializeSearchFunctionality();
                    
                    // Get the click handler
                    const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
                    
                    // Execute the search request
                    await clickHandler();
                    
                    // Verify request was made
                    expect(fetch).toHaveBeenCalled();
                    
                    // Parse request body
                    const fetchCall = fetch.mock.calls[fetch.mock.calls.length - 1];
                    const requestBody = JSON.parse(fetchCall[1].body);
                    
                    // Verify payload completeness even with edge case values
                    expect(requestBody).toHaveProperty('embedding_vector');
                    expect(requestBody).toHaveProperty('album_id');
                    expect(Array.isArray(requestBody.embedding_vector)).toBe(true);
                    expect(requestBody.embedding_vector.length).toBe(128);
                    expect(requestBody.album_id).toBe(albumId);
                    
                    // Verify all embedding values are finite
                    requestBody.embedding_vector.forEach(val => {
                        expect(typeof val).toBe('number');
                        expect(isFinite(val)).toBe(true);
                    });
                    
                    // Reset mocks for next iteration
                    fetch.mockClear();
                    mockSearchBtn.addEventListener.mockClear();
                }
            ),
            {
                numRuns: 25, // Run 25 edge case tests
                timeout: 10000,
                verbose: false
            }
        );
    });

    test('Property 5 Invariant: Payload structure consistency across multiple requests', async () => {
        // Test that multiple consecutive requests maintain the same payload structure
        const scenarios = await fc.sample(
            fc.record({
                albumId: fc.integer({ min: 1, max: 100 }),
                embedding: fc.array(fc.float({ min: -1, max: 1, noNaN: true, noDefaultInfinity: true }), { minLength: 128, maxLength: 128 })
            }),
            5 // Generate 5 scenarios
        );

        const payloadStructures = [];

        for (const scenario of scenarios) {
            // Normalize embedding to handle edge cases
            const normalizedEmbedding = scenario.embedding.map(val => {
                if (!isFinite(val)) return 0;
                if (val === 0) return 0; // Handle -0
                return val;
            });
            
            mockAlbumSelect.value = scenario.albumId.toString();
            setFaceEmbedding(normalizedEmbedding);
            
            // Mock successful response
            fetch.mockResolvedValueOnce({
                ok: true,
                json: jest.fn().mockResolvedValue({
                    success: true,
                    photos: []
                })
            });
            
            // Initialize search functionality
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the search request
            await clickHandler();
            
            // Capture payload structure
            const fetchCall = fetch.mock.calls[fetch.mock.calls.length - 1];
            const requestBody = JSON.parse(fetchCall[1].body);
            
            payloadStructures.push({
                keys: Object.keys(requestBody).sort(),
                embeddingLength: requestBody.embedding_vector.length,
                embeddingType: Array.isArray(requestBody.embedding_vector),
                albumIdType: typeof requestBody.album_id,
                allFinite: requestBody.embedding_vector.every(val => isFinite(val))
            });
            
            // Reset mocks for next iteration
            fetch.mockClear();
            mockSearchBtn.addEventListener.mockClear();
        }

        // Verify all payloads have identical structure
        const firstStructure = payloadStructures[0];
        payloadStructures.forEach((structure, index) => {
            expect(structure.keys).toEqual(firstStructure.keys);
            expect(structure.embeddingLength).toBe(128);
            expect(structure.embeddingType).toBe(true);
            expect(structure.albumIdType).toBe('number');
            expect(structure.allFinite).toBe(true);
        });
    });
});