/**
 * Tests for Task 6: Search Request Functionality
 * Tests the search button event listener, validation, POST request, and result display
 */

import { jest } from '@jest/globals';

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
    displayResults, 
    addToCart,
    getFaceEmbedding,
    setFaceEmbedding
} = await import('./face-scan.js');

describe('Task 6: Search Request Functionality', () => {
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
        
        // Set up global faceEmbedding for tests
        const testEmbedding = new Array(128).fill(0).map(() => Math.random());
        setFaceEmbedding(testEmbedding);
    });

    describe('initializeSearchFunctionality', () => {
        test('should add event listener to search button', () => {
            initializeSearchFunctionality();
            
            expect(mockSearchBtn.addEventListener).toHaveBeenCalledWith('click', expect.any(Function));
        });

        test('should handle missing DOM elements gracefully', () => {
            document.getElementById = jest.fn().mockReturnValue(null);
            
            initializeSearchFunctionality();
            
            expect(console.error).toHaveBeenCalledWith('Required DOM elements not found for search functionality');
        });

        test('should validate album selection before search', async () => {
            mockAlbumSelect.value = ''; // No album selected
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            expect(window.alert).toHaveBeenCalledWith('Pilih album terlebih dahulu');
            expect(fetch).not.toHaveBeenCalled();
        });

        test('should validate face embedding before search', async () => {
            mockAlbumSelect.value = '1';
            setFaceEmbedding(null); // No face embedding
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            expect(window.alert).toHaveBeenCalledWith('Silakan scan wajah Anda terlebih dahulu');
            expect(fetch).not.toHaveBeenCalled();
        });

        test('should send POST request with correct payload and headers', async () => {
            mockAlbumSelect.value = '5';
            const testEmbedding = new Array(128).fill(0).map(() => Math.random());
            setFaceEmbedding(testEmbedding);
            
            // Mock successful response
            fetch.mockResolvedValueOnce({
                ok: true,
                json: jest.fn().mockResolvedValue({
                    success: true,
                    photos: []
                })
            });
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            expect(fetch).toHaveBeenCalledWith('/face-scan/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': 'test-csrf-token',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    embedding_vector: testEmbedding,
                    album_id: 5
                })
            });
        });

        test('should display loading indicator during request', async () => {
            mockAlbumSelect.value = '1';
            
            // Mock delayed response
            fetch.mockImplementationOnce(() => 
                new Promise(resolve => {
                    setTimeout(() => {
                        resolve({
                            ok: true,
                            json: () => Promise.resolve({ success: true, photos: [] })
                        });
                    }, 100);
                })
            );
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Start the request (don't await yet)
            const requestPromise = clickHandler();
            
            // Check that loading is shown immediately
            expect(mockLoading.style.display).toBe('block');
            expect(mockSearchBtn.disabled).toBe(true);
            
            // Wait for request to complete
            await requestPromise;
            
            // Check that loading is hidden after completion
            expect(mockLoading.style.display).toBe('none');
            expect(mockSearchBtn.disabled).toBe(false);
        });

        test('should handle CSRF token missing error', async () => {
            mockAlbumSelect.value = '1';
            setFaceEmbedding(new Array(128).fill(0.5)); // Set valid embedding
            document.querySelector = jest.fn().mockReturnValue(null); // No CSRF token
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            expect(window.alert).toHaveBeenCalledWith('Sesi keamanan bermasalah. Silakan refresh halaman dan coba lagi.');
            expect(fetch).not.toHaveBeenCalled();
        });

        test('should handle network errors', async () => {
            mockAlbumSelect.value = '1';
            setFaceEmbedding(new Array(128).fill(0.5)); // Set valid embedding
            
            // Mock network error
            fetch.mockRejectedValueOnce(new Error('Network error'));
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            expect(window.alert).toHaveBeenCalledWith('Koneksi bermasalah. Silakan periksa internet Anda dan coba lagi.');
        });

        test('should handle server errors', async () => {
            mockAlbumSelect.value = '1';
            setFaceEmbedding(new Array(128).fill(0.5)); // Set valid embedding
            
            // Mock server error response
            fetch.mockResolvedValueOnce({
                ok: false,
                json: jest.fn().mockResolvedValue({
                    message: 'Server error'
                })
            });
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            expect(window.alert).toHaveBeenCalledWith('Pencarian gagal. Silakan coba lagi.');
        });

        test('should call displayResults with response data on success', async () => {
            mockAlbumSelect.value = '1';
            setFaceEmbedding(new Array(128).fill(0.5)); // Set valid embedding
            const mockPhotos = [
                { id: 1, watermark_path: '/path1.jpg', price: 50000, similarity: 0.85 },
                { id: 2, watermark_path: '/path2.jpg', price: 75000, similarity: 0.72 }
            ];
            
            // Mock createElement to return elements with innerHTML property
            const mockElements = [];
            document.createElement = jest.fn(() => {
                const element = {
                    className: '',
                    innerHTML: '',
                    style: {}
                };
                mockElements.push(element);
                return element;
            });
            
            // Mock successful response
            fetch.mockResolvedValueOnce({
                ok: true,
                json: jest.fn().mockResolvedValue({
                    success: true,
                    photos: mockPhotos
                })
            });
            
            initializeSearchFunctionality();
            
            // Get the click handler
            const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
            
            // Execute the click handler
            await clickHandler();
            
            // Verify displayResults was called with the photos
            expect(mockResultsContainer.style.display).toBe('block');
            expect(mockResultsDiv.appendChild).toHaveBeenCalledTimes(2);
            
            // Check that photo cards were created with correct content
            expect(mockElements.length).toBe(2);
            expect(mockElements[0].innerHTML).toContain('85.0% Match');
            expect(mockElements[1].innerHTML).toContain('72.0% Match');
        });
    });

    describe('displayResults', () => {
        test('should show results container', () => {
            const photos = [
                { id: 1, watermark_path: '/path1.jpg', price: 50000, similarity: 0.85 }
            ];
            
            displayResults(photos);
            
            expect(mockResultsContainer.style.display).toBe('block');
        });

        test('should display no matches message when photos array is empty', () => {
            displayResults([]);
            
            expect(mockResultsDiv.innerHTML).toContain('Tidak Ada Foto Ditemukan');
            expect(mockResultsDiv.innerHTML).toContain('tidak ada foto yang cocok');
        });

        test('should display no matches message when photos is null', () => {
            displayResults(null);
            
            expect(mockResultsDiv.innerHTML).toContain('Tidak Ada Foto Ditemukan');
        });

        test('should render photo cards with correct information', () => {
            const photos = [
                { id: 1, watermark_path: '/path1.jpg', price: 50000, similarity: 0.85 },
                { id: 2, watermark_path: '/path2.jpg', price: 75000, similarity: 0.72 }
            ];
            
            // Mock createElement and appendChild
            const mockElements = [];
            document.createElement = jest.fn((tag) => {
                const element = {
                    className: '',
                    innerHTML: '',
                    appendChild: jest.fn()
                };
                mockElements.push(element);
                return element;
            });
            
            mockResultsDiv.appendChild = jest.fn();
            
            displayResults(photos);
            
            expect(mockResultsDiv.appendChild).toHaveBeenCalledTimes(2);
            
            // Check first photo card content
            const firstCard = mockElements[0];
            expect(firstCard.innerHTML).toContain('/path1.jpg');
            expect(firstCard.innerHTML).toContain('85.0% Match');
            expect(firstCard.innerHTML).toContain('Rp 50.000');
            expect(firstCard.innerHTML).toContain('addToCart(1)');
            
            // Check second photo card content
            const secondCard = mockElements[1];
            expect(secondCard.innerHTML).toContain('/path2.jpg');
            expect(secondCard.innerHTML).toContain('72.0% Match');
            expect(secondCard.innerHTML).toContain('Rp 75.000');
            expect(secondCard.innerHTML).toContain('addToCart(2)');
        });

        test('should scroll to results container', () => {
            const photos = [
                { id: 1, watermark_path: '/path1.jpg', price: 50000, similarity: 0.85 }
            ];
            
            displayResults(photos);
            
            expect(mockResultsContainer.scrollIntoView).toHaveBeenCalledWith({ behavior: 'smooth' });
        });

        test('should handle missing DOM elements gracefully', () => {
            document.getElementById = jest.fn().mockReturnValue(null);
            
            displayResults([]);
            
            expect(console.error).toHaveBeenCalledWith('Results container elements not found');
        });
    });

    describe('addToCart', () => {
        test('should log photo ID and show placeholder alert', () => {
            addToCart(123);
            
            expect(console.log).toHaveBeenCalledWith('Adding photo to cart:', 123);
            expect(window.alert).toHaveBeenCalledWith('Foto dengan ID 123 akan ditambahkan ke keranjang (fitur belum diimplementasi)');
        });
    });

    describe('Property Tests - Search Request Payload Completeness', () => {
        test('Property 5: All search requests contain both embedding_vector and album_id', async () => {
            // Generate random search scenarios
            const scenarios = Array.from({ length: 10 }, (_, i) => ({
                albumId: Math.floor(Math.random() * 100) + 1,
                embedding: new Array(128).fill(0).map(() => Math.random())
            }));
            
            for (const scenario of scenarios) {
                mockAlbumSelect.value = scenario.albumId.toString();
                setFaceEmbedding(scenario.embedding);
                
                // Mock successful response
                fetch.mockResolvedValueOnce({
                    ok: true,
                    json: jest.fn().mockResolvedValue({ success: true, photos: [] })
                });
                
                initializeSearchFunctionality();
                
                // Get the click handler
                const clickHandler = mockSearchBtn.addEventListener.mock.calls[0][1];
                
                // Execute the click handler
                await clickHandler();
                
                // Verify request payload contains both required fields
                expect(fetch).toHaveBeenCalledWith('/face-scan/search', expect.objectContaining({
                    method: 'POST',
                    body: expect.stringContaining('"embedding_vector"')
                }));
                
                const requestBody = JSON.parse(fetch.mock.calls[fetch.mock.calls.length - 1][1].body);
                expect(requestBody).toHaveProperty('embedding_vector');
                expect(requestBody).toHaveProperty('album_id');
                expect(Array.isArray(requestBody.embedding_vector)).toBe(true);
                expect(typeof requestBody.album_id).toBe('number');
                
                // Reset for next iteration
                fetch.mockClear();
                mockSearchBtn.addEventListener.mockClear();
            }
        });
    });
});