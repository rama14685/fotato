/**
 * Unit Tests for Face Detection Error Handling - Task 5.2
 * Tests Requirements 1.5, 2.1: Error message display and retry functionality
 * @jest-environment jsdom
 */

import { jest } from '@jest/globals';

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

describe('Face Detection Error Handling - Task 5.2', () => {
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
   * Test error message display when no face is detected
   * Requirements: 1.5 - Error handling for face detection failures
   */
  describe('Error Message Display When No Face Detected', () => {
    test('should display Indonesian error message when no face is detected', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // Mock face detection returning null (no face detected)
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      const result = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(result).toBeNull();
      expect(global.alert).toHaveBeenCalledWith(
        'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
      );
      expect(global.alert).toHaveBeenCalledTimes(1);
    });

    test('should display error message exactly once per detection failure', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(global.alert).toHaveBeenCalledTimes(1);
      expect(global.alert).toHaveBeenCalledWith(
        'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
      );
    });

    test('should display error message for different image types when no face detected', async () => {
      // Test with different image element types
      const imageTypes = [
        document.createElement('img'),
        document.createElement('canvas'),
        document.createElement('video')
      ];
      
      for (const mockImage of imageTypes) {
        // Arrange
        jest.clearAllMocks();
        
        const mockWithFaceLandmarks = jest.fn().mockReturnValue({
          withFaceDescriptor: jest.fn().mockResolvedValue(null)
        });
        
        mockFaceApi.detectSingleFace.mockReturnValue({
          withFaceLandmarks: mockWithFaceLandmarks
        });
        
        // Act
        const result = await extractFaceEmbedding(mockImage, mockFaceApi);
        
        // Assert
        expect(result).toBeNull();
        expect(global.alert).toHaveBeenCalledWith(
          'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
        );
      }
    });

    test('should not display error message when face is successfully detected', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const validDescriptor = new Float32Array(128).fill(0.5);
      
      const mockDetection = {
        descriptor: validDescriptor,
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
      const result = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(result).not.toBeNull();
      expect(result).toHaveLength(128);
      expect(global.alert).not.toHaveBeenCalled();
    });

    test('should display error message when detection returns undefined', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(undefined)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      const result = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(result).toBeNull();
      expect(global.alert).toHaveBeenCalledWith(
        'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
      );
    });
  });

  /**
   * Test retry functionality after detection failure
   * Requirements: 2.1 - Face detection and embedding extraction
   */
  describe('Retry Functionality After Detection Failure', () => {
    test('should allow immediate retry after no face detected', async () => {
      // Arrange
      const mockImage1 = document.createElement('img');
      const mockImage2 = document.createElement('img');
      
      // First attempt: no face detected
      const mockWithFaceLandmarks1 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks1
      });
      
      // Act - First attempt
      const result1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(result1).toBeNull();
      expect(global.alert).toHaveBeenCalledTimes(1);
      
      // Arrange - Second attempt with successful detection
      const validDescriptor = new Float32Array(128).fill(0.7);
      const mockDetection = {
        descriptor: validDescriptor,
        detection: { score: 0.95 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks2 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks2
      });
      
      // Act - Second attempt (retry)
      const result2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(result2).not.toBeNull();
      expect(result2).toHaveLength(128);
      expect(result2[0]).toBeCloseTo(0.7, 5);
    });

    test('should allow multiple consecutive retry attempts', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act - Multiple retry attempts
      const results = [];
      for (let i = 0; i < 5; i++) {
        const result = await extractFaceEmbedding(mockImage, mockFaceApi);
        results.push(result);
      }
      
      // Assert - All attempts fail but function remains callable
      results.forEach(result => expect(result).toBeNull());
      expect(global.alert).toHaveBeenCalledTimes(5);
      expect(mockFaceApi.detectSingleFace).toHaveBeenCalledTimes(5);
    });

    test('should maintain independent state between retry attempts', async () => {
      // Arrange
      const mockImage1 = document.createElement('img');
      const mockImage2 = document.createElement('canvas');
      
      // First attempt: no face detected
      const mockWithFaceLandmarks1 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks1
      });
      
      // Act - First attempt
      const result1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(result1).toBeNull();
      
      // Arrange - Second attempt with different descriptor values
      const validDescriptor = new Float32Array(128);
      for (let i = 0; i < 128; i++) {
        validDescriptor[i] = Math.sin(i * 0.1); // Different values
      }
      
      const mockDetection = {
        descriptor: validDescriptor,
        detection: { score: 0.88 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks2 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks2
      });
      
      // Act - Second attempt (retry)
      const result2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds with independent values
      expect(result2).not.toBeNull();
      expect(result2).toHaveLength(128);
      expect(result2[0]).toBeCloseTo(Math.sin(0), 5);
      expect(result2[10]).toBeCloseTo(Math.sin(1), 5);
    });

    test('should allow retry after processing error', async () => {
      // Arrange
      const mockImage1 = document.createElement('img');
      const mockImage2 = document.createElement('img');
      
      // First attempt: processing error
      const processingError = new Error('Face processing failed');
      const mockWithFaceLandmarks1 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockRejectedValue(processingError)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks1
      });
      
      // Act - First attempt with error
      const result1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails with error
      expect(result1).toBeNull();
      expect(console.error).toHaveBeenCalledWith(
        'Error extracting face embedding:',
        processingError
      );
      expect(global.alert).toHaveBeenCalledWith(
        'Terjadi kesalahan saat memproses wajah. Silakan coba lagi.'
      );
      
      // Arrange - Second attempt succeeds
      jest.clearAllMocks();
      const validDescriptor = new Float32Array(128).fill(0.3);
      const mockDetection = {
        descriptor: validDescriptor,
        detection: { score: 0.92 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks2 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks2
      });
      
      // Act - Second attempt (retry after error)
      const result2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(result2).not.toBeNull();
      expect(result2).toHaveLength(128);
      expect(result2[0]).toBeCloseTo(0.3, 5);
    });

    test('should allow retry after invalid embedding dimension error', async () => {
      // Arrange
      const mockImage1 = document.createElement('img');
      const mockImage2 = document.createElement('img');
      
      // First attempt: invalid dimension
      const invalidDescriptor = new Float32Array(64); // Wrong size
      const mockDetection1 = {
        descriptor: invalidDescriptor,
        detection: { score: 0.9 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks1 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection1)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks1
      });
      
      // Act - First attempt with invalid dimension
      const result1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(result1).toBeNull();
      expect(console.error).toHaveBeenCalledWith(
        'Invalid embedding dimension: 64, expected 128'
      );
      
      // Arrange - Second attempt with valid dimension
      jest.clearAllMocks();
      const validDescriptor = new Float32Array(128).fill(0.8);
      const mockDetection2 = {
        descriptor: validDescriptor,
        detection: { score: 0.9 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks2 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection2)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValueOnce({
        withFaceLandmarks: mockWithFaceLandmarks2
      });
      
      // Act - Second attempt (retry)
      const result2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(result2).not.toBeNull();
      expect(result2).toHaveLength(128);
      expect(result2[0]).toBeCloseTo(0.8, 5);
    });

    test('should allow retry after models not loaded error', async () => {
      // Arrange
      resetModelsLoadedStatus();
      const mockImage1 = document.createElement('img');
      const mockImage2 = document.createElement('img');
      
      // Act - First attempt without models loaded
      const result1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(result1).toBeNull();
      expect(console.error).toHaveBeenCalledWith('Models not loaded yet');
      
      // Arrange - Load models and setup successful detection
      jest.clearAllMocks();
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);
      await loadModels(mockFaceApi);
      
      const validDescriptor = new Float32Array(128).fill(0.6);
      const mockDetection = {
        descriptor: validDescriptor,
        detection: { score: 0.93 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act - Second attempt after models loaded (retry)
      const result2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(result2).not.toBeNull();
      expect(result2).toHaveLength(128);
      expect(result2[0]).toBeCloseTo(0.6, 5);
    });

    test('should not interfere with successful detections during retry sequence', async () => {
      // Arrange - Mix of failed and successful attempts
      const images = [
        document.createElement('img'),
        document.createElement('canvas'),
        document.createElement('img'),
        document.createElement('video')
      ];
      
      const results = [];
      
      // Setup alternating success/failure pattern
      const mockCalls = [
        // First: fail
        () => {
          const mockWithFaceLandmarks = jest.fn().mockReturnValue({
            withFaceDescriptor: jest.fn().mockResolvedValue(null)
          });
          mockFaceApi.detectSingleFace.mockReturnValueOnce({
            withFaceLandmarks: mockWithFaceLandmarks
          });
        },
        // Second: success
        () => {
          const validDescriptor = new Float32Array(128).fill(0.1);
          const mockDetection = {
            descriptor: validDescriptor,
            detection: { score: 0.85 },
            landmarks: {}
          };
          const mockWithFaceLandmarks = jest.fn().mockReturnValue({
            withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
          });
          mockFaceApi.detectSingleFace.mockReturnValueOnce({
            withFaceLandmarks: mockWithFaceLandmarks
          });
        },
        // Third: fail
        () => {
          const mockWithFaceLandmarks = jest.fn().mockReturnValue({
            withFaceDescriptor: jest.fn().mockResolvedValue(null)
          });
          mockFaceApi.detectSingleFace.mockReturnValueOnce({
            withFaceLandmarks: mockWithFaceLandmarks
          });
        },
        // Fourth: success
        () => {
          const validDescriptor = new Float32Array(128).fill(0.9);
          const mockDetection = {
            descriptor: validDescriptor,
            detection: { score: 0.97 },
            landmarks: {}
          };
          const mockWithFaceLandmarks = jest.fn().mockReturnValue({
            withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
          });
          mockFaceApi.detectSingleFace.mockReturnValueOnce({
            withFaceLandmarks: mockWithFaceLandmarks
          });
        }
      ];
      
      // Act - Execute alternating pattern
      for (let i = 0; i < images.length; i++) {
        mockCalls[i]();
        const result = await extractFaceEmbedding(images[i], mockFaceApi);
        results.push(result);
      }
      
      // Assert - Pattern matches expectations
      expect(results[0]).toBeNull(); // First: fail
      expect(results[1]).not.toBeNull(); // Second: success
      expect(results[1]).toHaveLength(128);
      expect(results[1][0]).toBeCloseTo(0.1, 5);
      
      expect(results[2]).toBeNull(); // Third: fail
      expect(results[3]).not.toBeNull(); // Fourth: success
      expect(results[3]).toHaveLength(128);
      expect(results[3][0]).toBeCloseTo(0.9, 5);
      
      // Verify alert called only for failures
      expect(global.alert).toHaveBeenCalledTimes(2);
    });
  });

  /**
   * Test error message consistency and user experience
   */
  describe('Error Message Consistency', () => {
    test('should display consistent error message format across different failure scenarios', async () => {
      const scenarios = [
        {
          name: 'no face detected',
          setup: () => {
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(null)
            });
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
          }
        },
        {
          name: 'undefined detection result',
          setup: () => {
            const mockWithFaceLandmarks = jest.fn().mockReturnValue({
              withFaceDescriptor: jest.fn().mockResolvedValue(undefined)
            });
            mockFaceApi.detectSingleFace.mockReturnValue({
              withFaceLandmarks: mockWithFaceLandmarks
            });
          }
        }
      ];
      
      for (const scenario of scenarios) {
        // Arrange
        jest.clearAllMocks();
        const mockImage = document.createElement('img');
        scenario.setup();
        
        // Act
        const result = await extractFaceEmbedding(mockImage, mockFaceApi);
        
        // Assert - Same error message for all no-face scenarios
        expect(result).toBeNull();
        expect(global.alert).toHaveBeenCalledWith(
          'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
        );
      }
    });

    test('should display different error message for processing errors', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const processingError = new Error('Processing failed');
      
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockRejectedValue(processingError)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      const result = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert - Different error message for processing errors
      expect(result).toBeNull();
      expect(global.alert).toHaveBeenCalledWith(
        'Terjadi kesalahan saat memproses wajah. Silakan coba lagi.'
      );
      expect(console.error).toHaveBeenCalledWith(
        'Error extracting face embedding:',
        processingError
      );
    });
  });
});

/**
 * Unit Tests for Error Message Display - Task 15.2
 * Tests Requirements 10.1, 10.2, 10.3, 10.4:
 * - "Face not detected" message display (10.1)
 * - "Camera access denied" message display (10.2)
 * - "Search failed" message display (10.3)
 * - "Service unavailable" message display (10.4)
 * @jest-environment jsdom
 */
describe('Error Message Display - Task 15.2', () => {
  let showError, initializeCameraCapture, initializeSearchFunctionality, setFaceEmbedding;

  beforeEach(async () => {
    jest.clearAllMocks();

    // Reset DOM
    document.body.innerHTML = `
      <div id="errorMessage" style="display:none;"></div>
      <button id="startCamera">Camera</button>
      <video id="video" style="display:none;"></video>
      <canvas id="canvas" width="320" height="240"></canvas>
      <img id="preview" style="display:none;" />
      <input type="file" id="uploadFace" />
      <select id="albumSelect"><option value="1">Album 1</option></select>
      <button id="searchBtn" disabled>Search</button>
      <div id="loading" style="display:none;"></div>
      <div id="resultsContainer" style="display:none;"></div>
      <div id="results"></div>
      <meta name="csrf-token" content="test-csrf-token" />
    `;

    const module = await import('./face-scan.js');
    showError = module.showError;
    initializeCameraCapture = module.initializeCameraCapture;
    initializeSearchFunctionality = module.initializeSearchFunctionality;
    setFaceEmbedding = module.setFaceEmbedding;
  });

  /**
   * Test showError helper function
   * Requirements: 10.3, 10.4
   */
  describe('showError helper function', () => {
    test('should display the provided message in the errorMessage element', () => {
      const errorEl = document.getElementById('errorMessage');
      errorEl.style.display = 'none';

      showError('Search failed. Please try again');

      expect(errorEl.textContent).toBe('Search failed. Please try again');
      expect(errorEl.style.display).toBe('block');
    });

    test('should display "Service temporarily unavailable" message', () => {
      const errorEl = document.getElementById('errorMessage');

      showError('Service temporarily unavailable. Please try again later');

      expect(errorEl.textContent).toBe('Service temporarily unavailable. Please try again later');
      expect(errorEl.style.display).toBe('block');
    });

    test('should update message when called multiple times', () => {
      const errorEl = document.getElementById('errorMessage');

      showError('First error');
      expect(errorEl.textContent).toBe('First error');

      showError('Second error');
      expect(errorEl.textContent).toBe('Second error');
      expect(errorEl.style.display).toBe('block');
    });

    test('should create errorMessage element if it does not exist', () => {
      // Remove the existing errorMessage element
      const existing = document.getElementById('errorMessage');
      existing.remove();

      showError('Search failed. Please try again');

      const created = document.getElementById('errorMessage');
      expect(created).not.toBeNull();
      expect(created.textContent).toBe('Search failed. Please try again');
      expect(created.style.display).toBe('block');
    });
  });

  /**
   * Test "Camera access denied" message display
   * Requirements: 10.2
   */
  describe('"Camera access denied" message display', () => {
    test('should display camera access denied alert when getUserMedia is denied', async () => {
      initializeCameraCapture();

      const startCameraBtn = document.getElementById('startCamera');

      // Mock getUserMedia to reject with NotAllowedError
      const permissionError = new Error('Permission denied');
      permissionError.name = 'NotAllowedError';
      Object.defineProperty(global.navigator, 'mediaDevices', {
        value: {
          getUserMedia: jest.fn().mockRejectedValue(permissionError)
        },
        writable: true,
        configurable: true
      });

      startCameraBtn.click();
      await new Promise(resolve => setTimeout(resolve, 10));

      expect(global.alert).toHaveBeenCalledWith(
        'Akses kamera ditolak. Silakan periksa izin atau upload foto sebagai gantinya.'
      );
    });

    test('should display camera access denied alert for PermissionDeniedError', async () => {
      initializeCameraCapture();

      const startCameraBtn = document.getElementById('startCamera');

      const permissionError = new Error('Permission denied');
      permissionError.name = 'PermissionDeniedError';
      Object.defineProperty(global.navigator, 'mediaDevices', {
        value: {
          getUserMedia: jest.fn().mockRejectedValue(permissionError)
        },
        writable: true,
        configurable: true
      });

      startCameraBtn.click();
      await new Promise(resolve => setTimeout(resolve, 10));

      expect(global.alert).toHaveBeenCalledWith(
        'Akses kamera ditolak. Silakan periksa izin atau upload foto sebagai gantinya.'
      );
    });
  });

  /**
   * Test "Search failed" message display
   * Requirements: 10.3
   */
  describe('"Search failed" message display', () => {
    test('should display "Search failed" message when server returns non-503 error', async () => {
      // Set a face embedding so search can proceed
      setFaceEmbedding(new Array(128).fill(0.5));

      initializeSearchFunctionality();

      const searchBtn = document.getElementById('searchBtn');
      searchBtn.disabled = false;

      // Mock fetch to return a 500 error
      global.fetch = jest.fn().mockResolvedValue({
        ok: false,
        status: 500,
        json: jest.fn().mockResolvedValue({ success: false, message: 'Search failed. Please try again' })
      });

      searchBtn.click();
      await new Promise(resolve => setTimeout(resolve, 50));

      const errorEl = document.getElementById('errorMessage');
      expect(errorEl.textContent).toBe('Search failed. Please try again');
      expect(errorEl.style.display).toBe('block');
    });

    test('should display "Search failed" message when server returns 422 error', async () => {
      setFaceEmbedding(new Array(128).fill(0.5));

      initializeSearchFunctionality();

      const searchBtn = document.getElementById('searchBtn');
      searchBtn.disabled = false;

      global.fetch = jest.fn().mockResolvedValue({
        ok: false,
        status: 422,
        json: jest.fn().mockResolvedValue({ message: 'Validation failed' })
      });

      searchBtn.click();
      await new Promise(resolve => setTimeout(resolve, 50));

      const errorEl = document.getElementById('errorMessage');
      expect(errorEl.textContent).toBe('Search failed. Please try again');
      expect(errorEl.style.display).toBe('block');
    });
  });

  /**
   * Test "Service unavailable" message display
   * Requirements: 10.4
   */
  describe('"Service unavailable" message display', () => {
    test('should display "Service temporarily unavailable" when server returns 503', async () => {
      setFaceEmbedding(new Array(128).fill(0.5));

      initializeSearchFunctionality();

      const searchBtn = document.getElementById('searchBtn');
      searchBtn.disabled = false;

      global.fetch = jest.fn().mockResolvedValue({
        ok: false,
        status: 503,
        json: jest.fn().mockResolvedValue({ message: 'Service Unavailable' })
      });

      searchBtn.click();
      await new Promise(resolve => setTimeout(resolve, 50));

      const errorEl = document.getElementById('errorMessage');
      expect(errorEl.textContent).toBe('Service temporarily unavailable. Please try again later');
      expect(errorEl.style.display).toBe('block');
    });

    test('should display "Service temporarily unavailable" on network/fetch TypeError', async () => {
      setFaceEmbedding(new Array(128).fill(0.5));

      initializeSearchFunctionality();

      const searchBtn = document.getElementById('searchBtn');
      searchBtn.disabled = false;

      // Simulate a network failure (TypeError from fetch)
      global.fetch = jest.fn().mockRejectedValue(new TypeError('Failed to fetch'));

      searchBtn.click();
      await new Promise(resolve => setTimeout(resolve, 50));

      const errorEl = document.getElementById('errorMessage');
      expect(errorEl.textContent).toBe('Service temporarily unavailable. Please try again later');
      expect(errorEl.style.display).toBe('block');
    });
  });
});
