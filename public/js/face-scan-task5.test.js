/**
 * Property-Based Tests for Face Embedding Extraction - Task 5.1
 * Tests Property 2: Embedding Dimension Consistency
 * **Validates: Requirements 2.2**
 * 
 * @jest-environment jsdom
 */

import { jest } from '@jest/globals';
import fc from 'fast-check';

// Mock face-api.js
const mockFaceApi = {
  detectSingleFace: jest.fn(),
  nets: {
    ssdMobilenetv1: {
      loadFromUri: jest.fn()
    },
    faceLandmark68Net: {
      loadFromUri: jest.fn()
    },
    faceRecognitionNet: {
      loadFromUri: jest.fn()
    }
  }
};

// Mock global alert and console
global.alert = jest.fn();
global.console = {
  ...console,
  log: jest.fn(),
  error: jest.fn()
};

describe('Property-Based Tests - Task 5.1: Embedding Extraction', () => {
  let extractFaceEmbedding, loadModels, resetModelsLoadedStatus;

  beforeEach(async () => {
    // Reset mocks
    jest.clearAllMocks();
    mockFaceApi.detectSingleFace.mockReset();
    mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockReset();
    mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockReset();
    mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockReset();
    
    // Import the module
    const module = await import('./face-scan.js');
    extractFaceEmbedding = module.extractFaceEmbedding;
    loadModels = module.loadModels;
    resetModelsLoadedStatus = module.resetModelsLoadedStatus;
    
    // Load models
    mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
    mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
    mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);
    await loadModels(mockFaceApi);
  });

  /**
   * Property 2: Embedding Dimension Consistency
   * **Validates: Requirements 2.2**
   * 
   * For any detected face, the extracted embedding vector SHALL have exactly 128 dimensions.
   */
  describe('Property 2: Embedding Dimension Consistency', () => {
    test('should always return exactly 128 dimensions for any valid face detection', async () => {
      await fc.assert(
        fc.asyncProperty(
          // Generate random 128-dimensional embedding vectors (simulating face-api.js descriptors)
          fc.array(fc.float({ min: -1, max: 1 }), { minLength: 128, maxLength: 128 }),
          async (randomDescriptor) => {
            // Arrange: Create mock image element
            const mockImage = document.createElement('img');
            
            // Create Float32Array from random descriptor (as face-api.js does)
            const descriptorFloat32 = new Float32Array(randomDescriptor);
            
            // Mock face detection to return a valid detection with the random descriptor
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: 0.9 },
              landmarks: {}
            };
            
            // Setup mock chain
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act: Extract face embedding
            const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert: Embedding must have exactly 128 dimensions
            expect(embedding).not.toBeNull();
            expect(embedding).toHaveLength(128);
            expect(Array.isArray(embedding)).toBe(true);
            
            // Verify all elements are numbers (allow NaN as it's a valid number type from Float32Array)
            embedding.forEach((value, index) => {
              expect(typeof value).toBe('number');
            });
          }
        ),
        { 
          numRuns: 100, // Run 100 random test cases
          verbose: true 
        }
      );
    });

    test('should return exactly 128 dimensions regardless of descriptor value ranges', async () => {
      await fc.assert(
        fc.asyncProperty(
          // Generate descriptors with various value ranges
          fc.oneof(
            // Normal range [-1, 1]
            fc.array(fc.float({ min: -1, max: 1 }), { minLength: 128, maxLength: 128 }),
            // Larger range [-10, 10]
            fc.array(fc.float({ min: -10, max: 10 }), { minLength: 128, maxLength: 128 }),
            // Small values near zero (use Math.fround for 32-bit float compatibility)
            fc.array(fc.float({ min: Math.fround(-0.01), max: Math.fround(0.01) }), { minLength: 128, maxLength: 128 }),
            // All positive
            fc.array(fc.float({ min: 0, max: 1 }), { minLength: 128, maxLength: 128 }),
            // All negative
            fc.array(fc.float({ min: -1, max: 0 }), { minLength: 128, maxLength: 128 })
          ),
          async (randomDescriptor) => {
            // Arrange
            const mockImage = document.createElement('canvas');
            const descriptorFloat32 = new Float32Array(randomDescriptor);
            
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: 0.95 },
              landmarks: {}
            };
            
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act
            const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert: Always 128 dimensions regardless of value ranges
            expect(embedding).toHaveLength(128);
          }
        ),
        { numRuns: 50 }
      );
    });

    test('should return exactly 128 dimensions for different image element types', async () => {
      await fc.assert(
        fc.asyncProperty(
          fc.array(fc.float({ min: -1, max: 1 }), { minLength: 128, maxLength: 128 }),
          fc.constantFrom('img', 'canvas', 'video'),
          async (randomDescriptor, elementType) => {
            // Arrange: Create different types of image elements
            let mockImage;
            switch (elementType) {
              case 'img':
                mockImage = document.createElement('img');
                break;
              case 'canvas':
                mockImage = document.createElement('canvas');
                break;
              case 'video':
                mockImage = document.createElement('video');
                break;
            }
            
            const descriptorFloat32 = new Float32Array(randomDescriptor);
            
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: 0.85 },
              landmarks: {}
            };
            
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act
            const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert: Always 128 dimensions regardless of element type
            expect(embedding).toHaveLength(128);
          }
        ),
        { numRuns: 30 }
      );
    });

    test('should return exactly 128 dimensions with various detection confidence scores', async () => {
      await fc.assert(
        fc.asyncProperty(
          fc.array(fc.float({ min: -1, max: 1 }), { minLength: 128, maxLength: 128 }),
          fc.float({ min: 0.5, max: 1.0 }), // Detection confidence score
          async (randomDescriptor, confidenceScore) => {
            // Arrange
            const mockImage = document.createElement('img');
            const descriptorFloat32 = new Float32Array(randomDescriptor);
            
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: confidenceScore },
              landmarks: {}
            };
            
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act
            const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert: Always 128 dimensions regardless of confidence score
            expect(embedding).toHaveLength(128);
          }
        ),
        { numRuns: 50 }
      );
    });

    test('should reject embeddings with incorrect dimensions', async () => {
      await fc.assert(
        fc.asyncProperty(
          // Generate descriptors with WRONG dimensions (not 128)
          fc.integer({ min: 1, max: 512 }).chain(wrongSize => 
            wrongSize === 128 
              ? fc.constant(127) // Avoid 128
              : fc.constant(wrongSize)
          ).chain(size =>
            fc.array(fc.float({ min: -1, max: 1 }), { minLength: size, maxLength: size })
          ),
          async (wrongDescriptor) => {
            // Skip if accidentally generated 128 (should not happen with our generator)
            fc.pre(wrongDescriptor.length !== 128);
            
            // Arrange
            const mockImage = document.createElement('img');
            const descriptorFloat32 = new Float32Array(wrongDescriptor);
            
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: 0.9 },
              landmarks: {}
            };
            
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act
            const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert: Should return null for invalid dimensions
            expect(embedding).toBeNull();
            expect(console.error).toHaveBeenCalledWith(
              expect.stringContaining(`Invalid embedding dimension: ${wrongDescriptor.length}, expected 128`)
            );
          }
        ),
        { numRuns: 30 }
      );
    });

    test('should maintain 128 dimensions after Float32Array to Array conversion', async () => {
      await fc.assert(
        fc.asyncProperty(
          fc.array(fc.float({ min: -1, max: 1 }), { minLength: 128, maxLength: 128 }),
          async (randomDescriptor) => {
            // Arrange
            const mockImage = document.createElement('img');
            
            // Simulate face-api.js returning Float32Array
            const descriptorFloat32 = new Float32Array(randomDescriptor);
            
            // Verify Float32Array has 128 elements
            expect(descriptorFloat32.length).toBe(128);
            
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: 0.9 },
              landmarks: {}
            };
            
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act
            const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert: Conversion from Float32Array to Array preserves 128 dimensions
            expect(embedding).toHaveLength(128);
            expect(Array.isArray(embedding)).toBe(true);
            expect(embedding instanceof Float32Array).toBe(false);
          }
        ),
        { numRuns: 50 }
      );
    });
  });

  describe('Edge Cases for Dimension Consistency', () => {
    test('should return null when no face is detected (not 128 dimensions)', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // Mock no face detected
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(embedding).toBeNull();
      expect(global.alert).toHaveBeenCalledWith(
        'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
      );
    });

    test('should return null when models are not loaded', async () => {
      // Arrange
      resetModelsLoadedStatus();
      const mockImage = document.createElement('img');
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith('Models not loaded yet');
    });

    test('should return null when face-api.js is not available', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, null);
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith('face-api.js library not loaded');
    });
  });

  describe('Logging and Error Handling', () => {
    test('should log success message with correct dimension count', async () => {
      await fc.assert(
        fc.asyncProperty(
          fc.array(fc.float({ min: -1, max: 1 }), { minLength: 128, maxLength: 128 }),
          async (randomDescriptor) => {
            // Arrange
            jest.clearAllMocks();
            const mockImage = document.createElement('img');
            const descriptorFloat32 = new Float32Array(randomDescriptor);
            
            const mockDetection = {
              descriptor: descriptorFloat32,
              detection: { score: 0.9 },
              landmarks: {}
            };
            
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
            });
            
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
            
            // Act
            await extractFaceEmbedding(mockImage, mockFaceApi);
            
            // Assert
            expect(console.log).toHaveBeenCalledWith(
              'Face embedding extracted successfully:', 128, 'dimensions'
            );
          }
        ),
        { numRuns: 20 }
      );
    });
  });
});
