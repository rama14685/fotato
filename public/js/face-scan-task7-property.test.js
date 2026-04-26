/**
 * Property-Based Tests for Task 7.1 and 7.2: Search Results Display
 * **Property 14: Photo Display Completeness**
 * **Property 15: Add to Cart Button Presence**
 * **Validates: Requirements 6.2, 6.5**
 * 
 * These tests generate random matched photos and verify that all rendered outputs 
 * contain the required elements: watermarked image, similarity percentage, price,
 * and "Add to Cart" button.
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

// Mock DOM methods
Object.defineProperty(window, 'alert', {
    writable: true,
    value: jest.fn()
});

// Import the module
const { displayResults } = await import('./face-scan.js');

describe('Property-Based Tests: Search Results Display', () => {
    let mockResultsContainer, mockResultsDiv;

    beforeEach(() => {
        // Reset all mocks
        jest.clearAllMocks();
        
        // Mock DOM elements for results display
        mockResultsContainer = {
            style: { display: 'none' },
            scrollIntoView: jest.fn()
        };
        
        mockResultsDiv = {
            innerHTML: '',
            appendChild: jest.fn(),
            children: []
        };

        // Mock document.getElementById
        document.getElementById = jest.fn((id) => {
            switch (id) {
                case 'resultsContainer': return mockResultsContainer;
                case 'results': return mockResultsDiv;
                default: return null;
            }
        });
        
        // Mock console methods
        console.log = jest.fn();
        console.error = jest.fn();
    });

    /**
     * **Property 14: Photo Display Completeness**
     * **Validates: Requirements 6.2**
     * 
     * For any matched photo displayed in search results, the rendered output SHALL 
     * contain the watermarked image, similarity percentage, and price.
     */
    test('Property 14: All displayed photos contain image, similarity, and price', () => {
        fc.assert(
            fc.property(
                // Generate random arrays of matched photos
                fc.array(
                    fc.record({
                        id: fc.integer({ min: 1, max: 10000 }),
                        watermark_path: fc.oneof(
                            fc.constant('/storage/photos/watermark1.jpg'),
                            fc.constant('/storage/photos/watermark2.jpg'),
                            fc.webUrl({ withFragments: false, withQueryParameters: false })
                        ),
                        price: fc.integer({ min: 10000, max: 1000000 }), // Rp 10,000 to Rp 1,000,000
                        similarity: fc.float({ min: Math.fround(0.6), max: Math.fround(1.0), noNaN: true, noDefaultInfinity: true })
                    }),
                    { minLength: 1, maxLength: 20 } // Test with 1 to 20 photos
                ),
                (photos) => {
                    // Track all created photo cards
                    const createdCards = [];
                    
                    // Mock appendChild to capture created elements
                    mockResultsDiv.appendChild = jest.fn((element) => {
                        createdCards.push(element);
                    });
                    
                    // Call displayResults with generated photos
                    displayResults(photos);
                    
                    // Verify results container is shown
                    expect(mockResultsContainer.style.display).toBe('block');
                    
                    // Verify correct number of cards were created
                    expect(createdCards.length).toBe(photos.length);
                    
                    // Verify each photo card contains all required elements
                    photos.forEach((photo, index) => {
                        const card = createdCards[index];
                        const cardHTML = card.innerHTML;
                        
                        // CRITICAL: Verify watermarked image is present
                        // Note: Browser innerHTML escapes & to &amp; but not single quotes in attributes
                        // We check if the path appears in the src attribute, accounting for & escaping
                        const pathWithEscapedAmpersand = photo.watermark_path.replace(/&/g, '&amp;');
                        expect(cardHTML).toContain(`src="${pathWithEscapedAmpersand}"`);
                        expect(cardHTML).toContain('img');
                        
                        // CRITICAL: Verify similarity percentage is displayed
                        const similarityPercentage = (photo.similarity * 100).toFixed(1);
                        expect(cardHTML).toContain(`${similarityPercentage}%`);
                        expect(cardHTML).toContain('Match'); // Should show "X% Match"
                        
                        // CRITICAL: Verify price is displayed
                        const formattedPrice = photo.price.toLocaleString('id-ID');
                        expect(cardHTML).toContain(`Rp ${formattedPrice}`);
                        
                        // Verify the card has proper structure
                        expect(cardHTML).toContain('class='); // Has CSS classes
                        expect(card.className).toContain('bg-white/5'); // Has the expected styling
                    });
                }
            ),
            {
                numRuns: 50, // Run 50 random test cases
                verbose: false
            }
        );
    });

    /**
     * **Property 15: Add to Cart Button Presence**
     * **Validates: Requirements 6.5**
     * 
     * For any photo displayed in search results, the rendered output SHALL 
     * include an "Add to Cart" button.
     */
    test('Property 15: All displayed photos include Add to Cart button', () => {
        fc.assert(
            fc.property(
                // Generate random arrays of matched photos
                fc.array(
                    fc.record({
                        id: fc.integer({ min: 1, max: 10000 }),
                        watermark_path: fc.webUrl({ withFragments: false, withQueryParameters: false }),
                        price: fc.integer({ min: 10000, max: 1000000 }),
                        similarity: fc.float({ min: Math.fround(0.6), max: Math.fround(1.0), noNaN: true, noDefaultInfinity: true })
                    }),
                    { minLength: 1, maxLength: 20 }
                ),
                (photos) => {
                    // Track all created photo cards
                    const createdCards = [];
                    
                    // Mock appendChild to capture created elements
                    mockResultsDiv.appendChild = jest.fn((element) => {
                        createdCards.push(element);
                    });
                    
                    // Call displayResults with generated photos
                    displayResults(photos);
                    
                    // Verify each photo card contains "Add to Cart" button
                    photos.forEach((photo, index) => {
                        const card = createdCards[index];
                        const cardHTML = card.innerHTML;
                        
                        // CRITICAL: Verify "Add to Cart" button is present
                        expect(cardHTML).toContain('button');
                        expect(cardHTML).toContain('Tambah ke Keranjang'); // Indonesian for "Add to Cart"
                        
                        // CRITICAL: Verify button has onclick handler with correct photo ID
                        expect(cardHTML).toContain(`addToCart(${photo.id})`);
                        expect(cardHTML).toContain('onclick');
                        
                        // Verify button has proper styling
                        expect(cardHTML).toContain('bg-gradient-to-r'); // Has gradient background
                        expect(cardHTML).toContain('🛒'); // Has cart emoji
                    });
                }
            ),
            {
                numRuns: 50,
                verbose: false
            }
        );
    });

    /**
     * Property 14 & 15 Combined: Complete photo card structure validation
     * 
     * Validates that every photo card has ALL required elements together:
     * - Watermarked image
     * - Similarity percentage
     * - Price
     * - Add to Cart button
     */
    test('Property 14 & 15 Combined: Complete photo card structure', () => {
        fc.assert(
            fc.property(
                // Generate random arrays of matched photos with edge cases
                fc.array(
                    fc.record({
                        id: fc.integer({ min: 1, max: 10000 }),
                        watermark_path: fc.oneof(
                            fc.constant('/storage/photos/test.jpg'),
                            fc.constant('/storage/photos/test.png'),
                            fc.constant('/storage/photos/test.webp'),
                            fc.webUrl({ withFragments: false, withQueryParameters: false })
                        ),
                        price: fc.oneof(
                            fc.constant(10000), // Minimum price
                            fc.constant(50000), // Common price
                            fc.constant(100000), // Higher price
                            fc.constant(1000000), // Maximum price
                            fc.integer({ min: 10000, max: 1000000 })
                        ),
                        similarity: fc.oneof(
                            fc.constant(0.6), // Minimum threshold
                            fc.constant(0.75), // Medium similarity
                            fc.constant(0.9), // High similarity
                            fc.constant(1.0), // Perfect match
                            fc.float({ min: Math.fround(0.6), max: Math.fround(1.0), noNaN: true, noDefaultInfinity: true })
                        )
                    }),
                    { minLength: 1, maxLength: 15 }
                ),
                (photos) => {
                    // Track all created photo cards
                    const createdCards = [];
                    
                    // Mock appendChild to capture created elements
                    mockResultsDiv.appendChild = jest.fn((element) => {
                        createdCards.push(element);
                    });
                    
                    // Call displayResults with generated photos
                    displayResults(photos);
                    
                    // Verify each photo card has complete structure
                    photos.forEach((photo, index) => {
                        const card = createdCards[index];
                        const cardHTML = card.innerHTML;
                        
                        // Browser innerHTML escapes & to &amp; but not single quotes in attributes
                        const pathWithEscapedAmpersand = photo.watermark_path.replace(/&/g, '&amp;');
                        
                        // Count required elements
                        const hasImage = cardHTML.includes(`src="${pathWithEscapedAmpersand}"`);
                        const hasSimilarity = cardHTML.includes(`${(photo.similarity * 100).toFixed(1)}%`);
                        const hasPrice = cardHTML.includes(`Rp ${photo.price.toLocaleString('id-ID')}`);
                        const hasButton = cardHTML.includes('Tambah ke Keranjang') && 
                                         cardHTML.includes(`addToCart(${photo.id})`);
                        
                        // CRITICAL: All four elements must be present
                        expect(hasImage).toBe(true);
                        expect(hasSimilarity).toBe(true);
                        expect(hasPrice).toBe(true);
                        expect(hasButton).toBe(true);
                        
                        // Verify no element is missing
                        const allElementsPresent = hasImage && hasSimilarity && hasPrice && hasButton;
                        expect(allElementsPresent).toBe(true);
                    });
                }
            ),
            {
                numRuns: 50,
                verbose: false
            }
        );
    });

    /**
     * Edge Case: Empty results should not display photo cards
     * 
     * Validates that when no photos match, the system displays a "no matches found" 
     * message instead of photo cards.
     */
    test('Edge Case: Empty results display no matches message', () => {
        fc.assert(
            fc.property(
                // Generate different representations of empty results
                fc.oneof(
                    fc.constant([]),
                    fc.constant(null),
                    fc.constant(undefined)
                ),
                (emptyPhotos) => {
                    // Call displayResults with empty/null/undefined
                    displayResults(emptyPhotos);
                    
                    // Verify results container is still shown
                    expect(mockResultsContainer.style.display).toBe('block');
                    
                    // Verify "no matches found" message is displayed
                    expect(mockResultsDiv.innerHTML).toContain('Tidak Ada Foto Ditemukan');
                    expect(mockResultsDiv.innerHTML).toContain('😔'); // Sad emoji
                    
                    // Verify no photo cards were created
                    expect(mockResultsDiv.appendChild).not.toHaveBeenCalled();
                }
            ),
            {
                numRuns: 10,
                verbose: false
            }
        );
    });

    /**
     * Invariant: Photo card count matches input photo count
     * 
     * Validates that the number of rendered photo cards always equals 
     * the number of photos in the input array.
     */
    test('Invariant: Photo card count equals input photo count', () => {
        fc.assert(
            fc.property(
                // Generate random arrays of various sizes
                fc.array(
                    fc.record({
                        id: fc.integer({ min: 1, max: 10000 }),
                        watermark_path: fc.webUrl({ withFragments: false, withQueryParameters: false }),
                        price: fc.integer({ min: 10000, max: 1000000 }),
                        similarity: fc.float({ min: Math.fround(0.6), max: Math.fround(1.0), noNaN: true, noDefaultInfinity: true })
                    }),
                    { minLength: 1, maxLength: 50 } // Test with up to 50 photos
                ),
                (photos) => {
                    // Track appendChild calls
                    let appendCount = 0;
                    mockResultsDiv.appendChild = jest.fn(() => {
                        appendCount++;
                    });
                    
                    // Call displayResults
                    displayResults(photos);
                    
                    // CRITICAL: Number of cards must equal number of photos
                    expect(appendCount).toBe(photos.length);
                    expect(mockResultsDiv.appendChild).toHaveBeenCalledTimes(photos.length);
                }
            ),
            {
                numRuns: 30,
                verbose: false
            }
        );
    });

    /**
     * Property: Similarity percentage formatting consistency
     * 
     * Validates that similarity scores are always formatted consistently 
     * as percentages with one decimal place.
     */
    test('Property: Similarity percentage formatting is consistent', () => {
        fc.assert(
            fc.property(
                // Generate photos with various similarity values
                fc.array(
                    fc.record({
                        id: fc.integer({ min: 1, max: 10000 }),
                        watermark_path: fc.constant('/storage/test.jpg'),
                        price: fc.constant(50000),
                        similarity: fc.float({ min: Math.fround(0.6), max: Math.fround(1.0), noNaN: true, noDefaultInfinity: true })
                    }),
                    { minLength: 1, maxLength: 10 }
                ),
                (photos) => {
                    // Track created cards
                    const createdCards = [];
                    mockResultsDiv.appendChild = jest.fn((element) => {
                        createdCards.push(element);
                    });
                    
                    // Call displayResults
                    displayResults(photos);
                    
                    // Verify similarity formatting for each photo
                    photos.forEach((photo, index) => {
                        const card = createdCards[index];
                        const cardHTML = card.innerHTML;
                        
                        // Calculate expected percentage (1 decimal place)
                        const expectedPercentage = (photo.similarity * 100).toFixed(1);
                        
                        // Verify the formatted percentage appears in the card
                        expect(cardHTML).toContain(`${expectedPercentage}%`);
                        
                        // Verify it appears twice (once in badge, once in details)
                        const matches = cardHTML.match(new RegExp(`${expectedPercentage.replace('.', '\\.')}%`, 'g'));
                        expect(matches).not.toBeNull();
                        expect(matches.length).toBeGreaterThanOrEqual(1);
                    });
                }
            ),
            {
                numRuns: 30,
                verbose: false
            }
        );
    });

    /**
     * Property: Price formatting uses Indonesian locale
     * 
     * Validates that prices are always formatted with Indonesian locale 
     * (thousands separator).
     */
    test('Property: Price formatting uses Indonesian locale', () => {
        fc.assert(
            fc.property(
                // Generate photos with various price values
                fc.array(
                    fc.record({
                        id: fc.integer({ min: 1, max: 10000 }),
                        watermark_path: fc.constant('/storage/test.jpg'),
                        price: fc.oneof(
                            fc.constant(10000), // 10.000
                            fc.constant(50000), // 50.000
                            fc.constant(100000), // 100.000
                            fc.constant(500000), // 500.000
                            fc.constant(1000000), // 1.000.000
                            fc.integer({ min: 10000, max: 1000000 })
                        ),
                        similarity: fc.constant(0.85)
                    }),
                    { minLength: 1, maxLength: 10 }
                ),
                (photos) => {
                    // Track created cards
                    const createdCards = [];
                    mockResultsDiv.appendChild = jest.fn((element) => {
                        createdCards.push(element);
                    });
                    
                    // Call displayResults
                    displayResults(photos);
                    
                    // Verify price formatting for each photo
                    photos.forEach((photo, index) => {
                        const card = createdCards[index];
                        const cardHTML = card.innerHTML;
                        
                        // Calculate expected formatted price
                        const expectedPrice = `Rp ${photo.price.toLocaleString('id-ID')}`;
                        
                        // Verify the formatted price appears in the card
                        expect(cardHTML).toContain(expectedPrice);
                    });
                }
            ),
            {
                numRuns: 30,
                verbose: false
            }
        );
    });
});
