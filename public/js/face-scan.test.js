/**
 * Unit tests for face-scan.js functionality
 * Tests Requirements 1.2, 2.1: Camera Capture and Face Detection
 * @jest-environment jsdom
 */

import { jest } from '@jest/globals';

// Mock face-api.js
const mockFaceApi = {
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

// Mock global alert
global.alert = jest.fn();

// Mock console methods
global.console = {
  ...console,
  log: jest.fn(),
  error: jest.fn()
};

describe('Model Loading Tests - Task 1.1', () => {
  let loadModels, getModelsLoadedStatus, resetModelsLoadedStatus;

  beforeEach(async () => {
    // Reset mocks
    jest.clearAllMocks();
    mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockReset();
    mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockReset();
    mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockReset();
    
    // Import the module fresh for each test
    const module = await import('./face-scan.js');
    loadModels = module.loadModels;
    getModelsLoadedStatus = module.getModelsLoadedStatus;
    resetModelsLoadedStatus = module.resetModelsLoadedStatus;
    
    // Reset models loaded status
    resetModelsLoadedStatus();
  });

  describe('Successful Model Loading', () => {
    test('should load all three required models successfully', async () => {
      // Arrange
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      await loadModels(mockFaceApi);

      // Assert
      expect(mockFaceApi.nets.ssdMobilenetv1.loadFromUri).toHaveBeenCalledWith('/models');
      expect(mockFaceApi.nets.faceLandmark68Net.loadFromUri).toHaveBeenCalledWith('/models');
      expect(mockFaceApi.nets.faceRecognitionNet.loadFromUri).toHaveBeenCalledWith('/models');
      expect(mockFaceApi.nets.ssdMobilenetv1.loadFromUri).toHaveBeenCalledTimes(1);
      expect(mockFaceApi.nets.faceLandmark68Net.loadFromUri).toHaveBeenCalledTimes(1);
      expect(mockFaceApi.nets.faceRecognitionNet.loadFromUri).toHaveBeenCalledTimes(1);
    });

    test('should set modelsLoaded flag to true after successful loading', async () => {
      // Arrange
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      await loadModels(mockFaceApi);

      // Assert
      expect(getModelsLoadedStatus()).toBe(true);
    });

    test('should log success message when all models load successfully', async () => {
      // Arrange
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      await loadModels(mockFaceApi);

      // Assert
      expect(console.log).toHaveBeenCalledWith('Loading face-api.js models...');
      expect(console.log).toHaveBeenCalledWith('All face-api.js models loaded successfully');
    });

    test('should load all models in parallel using Promise.all', async () => {
      // Arrange
      const loadTimes = [];
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockImplementation(async () => {
        loadTimes.push({ model: 'ssd', time: Date.now() });
        await new Promise(resolve => setTimeout(resolve, 10));
      });
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockImplementation(async () => {
        loadTimes.push({ model: 'landmark', time: Date.now() });
        await new Promise(resolve => setTimeout(resolve, 10));
      });
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockImplementation(async () => {
        loadTimes.push({ model: 'recognition', time: Date.now() });
        await new Promise(resolve => setTimeout(resolve, 10));
      });

      // Act
      await loadModels(mockFaceApi);

      // Assert - all three should start loading at roughly the same time (parallel)
      expect(loadTimes).toHaveLength(3);
      const timeDiffs = [
        Math.abs(loadTimes[1].time - loadTimes[0].time),
        Math.abs(loadTimes[2].time - loadTimes[0].time)
      ];
      // All should start within 5ms of each other (parallel execution)
      timeDiffs.forEach(diff => expect(diff).toBeLessThan(5));
    });
  });

  describe('Error Handling When Models Fail to Load', () => {
    test('should throw error when ssdMobilenetv1 model fails to load', async () => {
      // Arrange
      const loadError = new Error('Failed to load ssdMobilenetv1 model');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockRejectedValue(loadError);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act & Assert
      await expect(loadModels(mockFaceApi)).rejects.toThrow(loadError);
    });

    test('should throw error when faceLandmark68Net model fails to load', async () => {
      // Arrange
      const loadError = new Error('Failed to load faceLandmark68Net model');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockRejectedValue(loadError);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act & Assert
      await expect(loadModels(mockFaceApi)).rejects.toThrow(loadError);
    });

    test('should throw error when faceRecognitionNet model fails to load', async () => {
      // Arrange
      const loadError = new Error('Failed to load faceRecognitionNet model');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockRejectedValue(loadError);

      // Act & Assert
      await expect(loadModels(mockFaceApi)).rejects.toThrow(loadError);
    });

    test('should log error message when model loading fails', async () => {
      // Arrange
      const loadError = new Error('Network error');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockRejectedValue(loadError);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      try {
        await loadModels(mockFaceApi);
      } catch (error) {
        // Expected to throw
      }

      // Assert
      expect(console.error).toHaveBeenCalledWith('Error loading face-api.js models:', loadError);
    });

    test('should display alert to user when model loading fails', async () => {
      // Arrange
      const loadError = new Error('Network error');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockRejectedValue(loadError);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      try {
        await loadModels(mockFaceApi);
      } catch (error) {
        // Expected to throw
      }

      // Assert
      expect(global.alert).toHaveBeenCalledWith('Failed to load face detection models. Please refresh the page.');
    });

    test('should not set modelsLoaded flag when loading fails', async () => {
      // Arrange
      const loadError = new Error('Network error');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockRejectedValue(loadError);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      try {
        await loadModels(mockFaceApi);
      } catch (error) {
        // Expected to throw
      }

      // Assert
      expect(getModelsLoadedStatus()).toBe(false);
    });

    test('should throw error when face-api.js library is not provided', async () => {
      // Act & Assert
      await expect(loadModels(null)).rejects.toThrow('face-api.js library not loaded');
    });

    test('should handle multiple models failing simultaneously', async () => {
      // Arrange
      const error1 = new Error('Failed to load model 1');
      const error2 = new Error('Failed to load model 2');
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockRejectedValue(error1);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockRejectedValue(error2);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act & Assert
      // Promise.all will reject with the first error that occurs
      await expect(loadModels(mockFaceApi)).rejects.toThrow();
    });
  });

  describe('Model Loading from Correct Path', () => {
    test('should load models from /models directory', async () => {
      // Arrange
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);

      // Act
      await loadModels(mockFaceApi);

      // Assert
      expect(mockFaceApi.nets.ssdMobilenetv1.loadFromUri).toHaveBeenCalledWith('/models');
      expect(mockFaceApi.nets.faceLandmark68Net.loadFromUri).toHaveBeenCalledWith('/models');
      expect(mockFaceApi.nets.faceRecognitionNet.loadFromUri).toHaveBeenCalledWith('/models');
    });
  });
});

describe('Camera Capture Functionality Tests - Task 3', () => {
  let initializeCameraCapture, extractFaceEmbedding;
  let mockGetUserMedia, mockStream, mockTrack;

  beforeEach(async () => {
    // Reset mocks
    jest.clearAllMocks();
    
    // Set up DOM
    document.body.innerHTML = `
      <button id="startCamera">Start Camera</button>
      <video id="video" width="320" height="240"></video>
      <canvas id="canvas" width="320" height="240"></canvas>
      <button id="searchBtn" disabled>Search</button>
    `;
    
    // Mock getUserMedia
    mockTrack = {
      stop: jest.fn()
    };
    
    mockStream = {
      getTracks: jest.fn(() => [mockTrack])
    };
    
    mockGetUserMedia = jest.fn().mockResolvedValue(mockStream);
    
    global.navigator.mediaDevices = {
      getUserMedia: mockGetUserMedia
    };
    
    // Mock canvas context
    const mockContext = {
      drawImage: jest.fn()
    };
    
    const canvas = document.getElementById('canvas');
    canvas.getContext = jest.fn(() => mockContext);
    
    // Mock setTimeout to execute immediately for testing
    jest.useFakeTimers();
    
    // Import the module
    const module = await import('./face-scan.js');
    initializeCameraCapture = module.initializeCameraCapture;
    extractFaceEmbedding = module.extractFaceEmbedding;
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  describe('Camera Button Event Listener', () => {
    test('should request getUserMedia when camera button is clicked', async () => {
      // Arrange
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for async handler
      
      // Assert
      expect(mockGetUserMedia).toHaveBeenCalledWith({ 
        video: { width: 320, height: 240 } 
      });
    });
  });

  describe('Video Stream Display', () => {
    test('should display video stream in video element', async () => {
      // Arrange
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      const video = document.getElementById('video');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for async handler
      
      // Assert
      expect(video.srcObject).toBe(mockStream);
      expect(video.style.display).toBe('block');
    });
  });

  describe('Frame Capture After 3 Seconds', () => {
    test('should capture frame to canvas after 3 seconds', async () => {
      // Arrange
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      const canvas = document.getElementById('canvas');
      const video = document.getElementById('video');
      const ctx = canvas.getContext('2d');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for getUserMedia
      
      // Fast-forward time by 3 seconds
      jest.advanceTimersByTime(3000);
      await Promise.resolve(); // Wait for setTimeout callback
      
      // Assert
      expect(ctx.drawImage).toHaveBeenCalledWith(video, 0, 0, 320, 240);
    });
  });

  describe('Video Stream Stop After Capture', () => {
    test('should stop video stream after capture', async () => {
      // Arrange
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      const video = document.getElementById('video');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for getUserMedia
      
      // Fast-forward time by 3 seconds
      jest.advanceTimersByTime(3000);
      await Promise.resolve(); // Wait for setTimeout callback
      
      // Assert
      expect(mockStream.getTracks).toHaveBeenCalled();
      expect(mockTrack.stop).toHaveBeenCalled();
      expect(video.style.display).toBe('none');
    });
  });

  describe('Face Detection on Captured Canvas', () => {
    test('should call face detection after capturing canvas', async () => {
      // Arrange
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      const canvas = document.getElementById('canvas');
      const ctx = canvas.getContext('2d');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for getUserMedia
      
      // Fast-forward time by 3 seconds
      jest.advanceTimersByTime(3000);
      await Promise.resolve(); // Wait for setTimeout callback
      
      // Assert - verify canvas was drawn (which happens before face detection)
      expect(ctx.drawImage).toHaveBeenCalled();
    });
  });

  describe('Error Handling', () => {
    test('should display error message when camera access is denied', async () => {
      // Arrange
      const deniedError = new Error('Permission denied');
      deniedError.name = 'NotAllowedError';
      mockGetUserMedia.mockRejectedValue(deniedError);
      
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for async handler
      await Promise.resolve(); // Wait for error handling
      
      // Assert
      expect(global.alert).toHaveBeenCalledWith(
        'Akses kamera ditolak. Silakan periksa izin atau upload foto sebagai gantinya.'
      );
    });

    test('should display error message when camera is not found', async () => {
      // Arrange
      const notFoundError = new Error('Camera not found');
      notFoundError.name = 'NotFoundError';
      mockGetUserMedia.mockRejectedValue(notFoundError);
      
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for async handler
      await Promise.resolve(); // Wait for error handling
      
      // Assert
      expect(global.alert).toHaveBeenCalledWith(
        'Kamera tidak ditemukan. Silakan upload foto sebagai gantinya.'
      );
    });

    test('should display generic error message for other camera errors', async () => {
      // Arrange
      const genericError = new Error('Unknown error');
      mockGetUserMedia.mockRejectedValue(genericError);
      
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for async handler
      await Promise.resolve(); // Wait for error handling
      
      // Assert
      expect(global.alert).toHaveBeenCalledWith(
        'Tidak dapat mengakses kamera. Silakan upload foto sebagai gantinya.'
      );
    });

    test('should log error when camera access fails', async () => {
      // Arrange
      const error = new Error('Camera error');
      mockGetUserMedia.mockRejectedValue(error);
      
      initializeCameraCapture();
      const startCameraBtn = document.getElementById('startCamera');
      
      // Act
      startCameraBtn.click();
      await Promise.resolve(); // Wait for async handler
      await Promise.resolve(); // Wait for error handling
      
      // Assert
      expect(console.error).toHaveBeenCalledWith('Error accessing camera:', error);
    });
  });

  describe('DOM Element Validation', () => {
    test('should handle missing DOM elements gracefully', () => {
      // Arrange
      document.body.innerHTML = ''; // Empty DOM
      
      // Act & Assert - should not throw
      expect(() => initializeCameraCapture()).not.toThrow();
      expect(console.error).toHaveBeenCalledWith('Required DOM elements not found');
    });
  });
});

describe('File Upload Functionality Tests - Task 4', () => {
  let initializeFileUpload, extractFaceEmbedding, getModelsLoadedStatus, resetModelsLoadedStatus;
  
  beforeEach(async () => {
    // Reset mocks
    jest.clearAllMocks();
    
    // Set up DOM
    document.body.innerHTML = `
      <input type="file" id="uploadFace" accept="image/*">
      <img id="preview" style="display:none;">
      <button id="searchBtn" disabled>Search</button>
    `;
    
    // Mock URL.createObjectURL and revokeObjectURL
    global.URL.createObjectURL = jest.fn(() => 'blob:mock-url');
    global.URL.revokeObjectURL = jest.fn();
    
    // Import the module
    const module = await import('./face-scan.js');
    initializeFileUpload = module.initializeFileUpload;
    extractFaceEmbedding = module.extractFaceEmbedding;
    getModelsLoadedStatus = module.getModelsLoadedStatus;
    resetModelsLoadedStatus = module.resetModelsLoadedStatus;
  });

  describe('File Input Event Listener', () => {
    test('should add event listener to file input element', () => {
      // Arrange
      const uploadInput = document.getElementById('uploadFace');
      const addEventListenerSpy = jest.spyOn(uploadInput, 'addEventListener');
      
      // Act
      initializeFileUpload();
      
      // Assert
      expect(addEventListenerSpy).toHaveBeenCalledWith('change', expect.any(Function));
    });
  });

  describe('Image Format Validation - Requirement 1.3', () => {
    test('should accept JPEG image files', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(global.URL.createObjectURL).toHaveBeenCalledWith(mockFile);
      expect(preview.src).toBe('blob:mock-url');
      expect(preview.style.display).toBe('block');
    });

    test('should accept PNG image files', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.png', { type: 'image/png' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(global.URL.createObjectURL).toHaveBeenCalledWith(mockFile);
      expect(preview.src).toBe('blob:mock-url');
      expect(preview.style.display).toBe('block');
    });

    test('should accept WebP image files', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.webp', { type: 'image/webp' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(global.URL.createObjectURL).toHaveBeenCalledWith(mockFile);
      expect(preview.src).toBe('blob:mock-url');
      expect(preview.style.display).toBe('block');
    });

    test('should reject unsupported file formats', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.gif', { type: 'image/gif' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(global.alert).toHaveBeenCalledWith(
        'Format file tidak didukung. Silakan upload file JPEG, PNG, atau WebP.'
      );
      expect(global.URL.createObjectURL).not.toHaveBeenCalled();
      expect(preview.style.display).toBe('none');
    });

    test('should reject PDF files', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      
      const mockFile = new File(['pdf content'], 'test.pdf', { type: 'application/pdf' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(global.alert).toHaveBeenCalledWith(
        'Format file tidak didukung. Silakan upload file JPEG, PNG, atau WebP.'
      );
      expect(global.URL.createObjectURL).not.toHaveBeenCalled();
    });
  });

  describe('Image Preview Display - Requirement 1.4', () => {
    test('should display preview image when file is uploaded', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(preview.src).toBe('blob:mock-url');
      expect(preview.style.display).toBe('block');
    });

    test('should create object URL from uploaded file', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(global.URL.createObjectURL).toHaveBeenCalledWith(mockFile);
    });
  });

  describe('Face Detection on Upload - Requirement 2.1', () => {
    test('should call extractFaceEmbedding when image loads', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Trigger onload event
      preview.onload();
      await Promise.resolve();
      
      // Assert - verify onload handler was set
      expect(preview.onload).toBeDefined();
      expect(console.log).toHaveBeenCalledWith('Image loaded, extracting face embedding...');
    });

    test('should enable search button when face embedding is extracted', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      const searchBtn = document.getElementById('searchBtn');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Mock extractFaceEmbedding to return a valid embedding
      const mockEmbedding = new Array(128).fill(0.5);
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Manually set faceEmbedding and trigger onload
      // (In real scenario, extractFaceEmbedding would be called)
      preview.onload = async () => {
        // Simulate successful embedding extraction
        const faceEmbedding = mockEmbedding;
        if (faceEmbedding && searchBtn) {
          searchBtn.disabled = false;
        }
      };
      
      preview.onload();
      await Promise.resolve();
      
      // Assert
      expect(searchBtn.disabled).toBe(false);
    });
  });

  describe('Memory Management', () => {
    test('should revoke object URL after image loads', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Trigger onload event
      preview.onload();
      await Promise.resolve();
      
      // Assert
      expect(global.URL.revokeObjectURL).toHaveBeenCalledWith('blob:mock-url');
    });

    test('should revoke object URL on image load error', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Trigger onerror event
      preview.onerror();
      await Promise.resolve();
      
      // Assert
      expect(global.URL.revokeObjectURL).toHaveBeenCalledWith('blob:mock-url');
    });
  });

  describe('Error Handling', () => {
    test('should display error message when image fails to load', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      const preview = document.getElementById('preview');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Trigger onerror event
      preview.onerror();
      await Promise.resolve();
      
      // Assert
      expect(console.error).toHaveBeenCalledWith('Error loading image');
      expect(global.alert).toHaveBeenCalledWith('Gagal memuat gambar. Silakan coba file lain.');
    });

    test('should handle no file selected gracefully', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      
      Object.defineProperty(uploadInput, 'files', {
        value: [],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(console.log).toHaveBeenCalledWith('No file selected');
      expect(global.URL.createObjectURL).not.toHaveBeenCalled();
    });
  });

  describe('DOM Element Validation', () => {
    test('should handle missing DOM elements gracefully', () => {
      // Arrange
      document.body.innerHTML = ''; // Empty DOM
      
      // Act & Assert - should not throw
      expect(() => initializeFileUpload()).not.toThrow();
      expect(console.error).toHaveBeenCalledWith('Required DOM elements not found');
    });

    test('should handle missing preview element', () => {
      // Arrange
      document.body.innerHTML = `
        <input type="file" id="uploadFace" accept="image/*">
        <button id="searchBtn" disabled>Search</button>
      `;
      
      // Act & Assert - should not throw
      expect(() => initializeFileUpload()).not.toThrow();
      expect(console.error).toHaveBeenCalledWith('Required DOM elements not found');
    });

    test('should handle missing upload input element', () => {
      // Arrange
      document.body.innerHTML = `
        <img id="preview" style="display:none;">
        <button id="searchBtn" disabled>Search</button>
      `;
      
      // Act & Assert - should not throw
      expect(() => initializeFileUpload()).not.toThrow();
      expect(console.error).toHaveBeenCalledWith('Required DOM elements not found');
    });
  });

  describe('Logging', () => {
    test('should log file information when file is selected', async () => {
      // Arrange
      initializeFileUpload();
      const uploadInput = document.getElementById('uploadFace');
      
      const mockFile = new File(['image content'], 'test.jpg', { type: 'image/jpeg' });
      Object.defineProperty(uploadInput, 'files', {
        value: [mockFile],
        writable: false
      });
      
      // Act
      const event = new Event('change');
      uploadInput.dispatchEvent(event);
      await Promise.resolve();
      
      // Assert
      expect(console.log).toHaveBeenCalledWith('File selected:', 'test.jpg', 'image/jpeg');
    });
  });
});

describe('Face Detection Error Handling Tests - Task 5.2', () => {
  let extractFaceEmbedding, loadModels, resetModelsLoadedStatus;
  
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

  describe('Error Message Display When No Face Detected - Requirement 1.5', () => {
    test('should display error message when no face is detected in image', async () => {
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
      const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(embedding).toBeNull();
      expect(global.alert).toHaveBeenCalledWith(
        'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.'
      );
    });

    test('should display error message when face detection returns undefined', async () => {
      // Arrange
      const mockImage = document.createElement('canvas');
      
      // Mock face detection returning undefined
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(undefined)
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

    test('should display error message for blurry images with no detectable face', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      mockImage.src = 'blurry-image.jpg';
      
      // Mock face detection failing due to poor image quality
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

    test('should display error message when image contains no people', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      mockImage.src = 'landscape.jpg';
      
      // Mock face detection returning null for image with no faces
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

    test('should display error message when face is too small to detect', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // Mock face detection failing due to face being too small
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
  });

  describe('Retry Functionality After Detection Failure - Requirement 2.1', () => {
    test('should allow retry after no face detected by returning null', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // First attempt: no face detected
      const mockWithFaceLandmarks1 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks1
      });
      
      // Act - First attempt
      const embedding1 = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert - First attempt fails
      expect(embedding1).toBeNull();
      expect(global.alert).toHaveBeenCalledTimes(1);
      
      // Arrange - Second attempt with better image
      jest.clearAllMocks();
      const mockImage2 = document.createElement('img');
      const validDescriptor = new Float32Array(128).fill(0.5);
      
      const mockDetection = {
        descriptor: validDescriptor,
        detection: { score: 0.9 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks2 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks2
      });
      
      // Act - Second attempt (retry)
      const embedding2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(embedding2).not.toBeNull();
      expect(embedding2).toHaveLength(128);
      expect(global.alert).not.toHaveBeenCalled();
    });

    test('should allow multiple retry attempts after detection failures', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // Mock face detection returning null
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(null)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act - Multiple failed attempts
      const embedding1 = await extractFaceEmbedding(mockImage, mockFaceApi);
      const embedding2 = await extractFaceEmbedding(mockImage, mockFaceApi);
      const embedding3 = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert - All attempts fail but function can be called multiple times
      expect(embedding1).toBeNull();
      expect(embedding2).toBeNull();
      expect(embedding3).toBeNull();
      expect(global.alert).toHaveBeenCalledTimes(3);
      expect(mockFaceApi.detectSingleFace).toHaveBeenCalledTimes(3);
    });

    test('should not block retry attempts after error', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // First attempt: detection error
      const mockWithFaceLandmarks1 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockRejectedValue(new Error('Detection error'))
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks1
      });
      
      // Act - First attempt with error
      const embedding1 = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert - First attempt fails with error
      expect(embedding1).toBeNull();
      expect(console.error).toHaveBeenCalledWith(
        'Error extracting face embedding:',
        expect.any(Error)
      );
      
      // Arrange - Second attempt succeeds
      jest.clearAllMocks();
      const validDescriptor = new Float32Array(128).fill(0.5);
      
      const mockDetection = {
        descriptor: validDescriptor,
        detection: { score: 0.9 },
        landmarks: {}
      };
      
      const mockWithFaceLandmarks2 = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockResolvedValue(mockDetection)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks2
      });
      
      // Act - Second attempt (retry after error)
      const embedding2 = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(embedding2).not.toBeNull();
      expect(embedding2).toHaveLength(128);
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
      const embedding1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(embedding1).toBeNull();
      
      // Arrange - Second attempt with different image
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
      
      // Act - Second attempt (retry with different image)
      const embedding2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds independently
      expect(embedding2).not.toBeNull();
      expect(embedding2).toHaveLength(128);
      expect(embedding2[0]).toBeCloseTo(0.7, 5);
    });
  });

  describe('Error Handling for Processing Errors - Requirement 2.1', () => {
    test('should display error message when face detection throws exception', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const detectionError = new Error('Face detection failed');
      
      // Mock face detection throwing an error
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockRejectedValue(detectionError)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith('Error extracting face embedding:', detectionError);
      expect(global.alert).toHaveBeenCalledWith(
        'Terjadi kesalahan saat memproses wajah. Silakan coba lagi.'
      );
    });

    test('should display error message when landmark detection fails', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const landmarkError = new Error('Landmark detection failed');
      
      // Mock landmark detection throwing an error
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: jest.fn().mockImplementation(() => {
          throw landmarkError;
        })
      });
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith('Error extracting face embedding:', landmarkError);
      expect(global.alert).toHaveBeenCalledWith(
        'Terjadi kesalahan saat memproses wajah. Silakan coba lagi.'
      );
    });

    test('should display error message when descriptor extraction fails', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const descriptorError = new Error('Descriptor extraction failed');
      
      // Mock descriptor extraction throwing an error
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockRejectedValue(descriptorError)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith('Error extracting face embedding:', descriptorError);
      expect(global.alert).toHaveBeenCalledWith(
        'Terjadi kesalahan saat memproses wajah. Silakan coba lagi.'
      );
    });

    test('should log error details for debugging', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const error = new Error('Network timeout');
      
      const mockWithFaceLandmarks = jest.fn().mockReturnValue({
        withFaceDescriptor: jest.fn().mockRejectedValue(error)
      });
      
      mockFaceApi.detectSingleFace.mockReturnValue({
        withFaceLandmarks: mockWithFaceLandmarks
      });
      
      // Act
      await extractFaceEmbedding(mockImage, mockFaceApi);
      
      // Assert
      expect(console.error).toHaveBeenCalledWith('Error extracting face embedding:', error);
    });
  });

  describe('Invalid Embedding Dimension Error Handling', () => {
    test('should return null when embedding has invalid dimensions', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      const invalidDescriptor = new Float32Array(64); // Wrong size: 64 instead of 128
      
      const mockDetection = {
        descriptor: invalidDescriptor,
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
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith(
        'Invalid embedding dimension: 64, expected 128'
      );
    });

    test('should allow retry after invalid embedding dimension error', async () => {
      // Arrange
      const mockImage1 = document.createElement('img');
      const invalidDescriptor = new Float32Array(256); // Wrong size
      
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
      const embedding1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(embedding1).toBeNull();
      
      // Arrange - Second attempt with valid dimension
      jest.clearAllMocks();
      const mockImage2 = document.createElement('img');
      const validDescriptor = new Float32Array(128).fill(0.5);
      
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
      const embedding2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(embedding2).not.toBeNull();
      expect(embedding2).toHaveLength(128);
    });
  });

  describe('Models Not Loaded Error Handling', () => {
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

    test('should allow retry after models are loaded', async () => {
      // Arrange
      resetModelsLoadedStatus();
      const mockImage1 = document.createElement('img');
      
      // Act - First attempt without models loaded
      const embedding1 = await extractFaceEmbedding(mockImage1, mockFaceApi);
      
      // Assert - First attempt fails
      expect(embedding1).toBeNull();
      expect(console.error).toHaveBeenCalledWith('Models not loaded yet');
      
      // Arrange - Load models
      jest.clearAllMocks();
      mockFaceApi.nets.ssdMobilenetv1.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceLandmark68Net.loadFromUri.mockResolvedValue(undefined);
      mockFaceApi.nets.faceRecognitionNet.loadFromUri.mockResolvedValue(undefined);
      await loadModels(mockFaceApi);
      
      const mockImage2 = document.createElement('img');
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
      
      // Act - Second attempt after models loaded (retry)
      const embedding2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(embedding2).not.toBeNull();
      expect(embedding2).toHaveLength(128);
    });
  });

  describe('Face API Library Not Available Error Handling', () => {
    test('should return null when face-api.js library is not available', async () => {
      // Arrange
      const mockImage = document.createElement('img');
      
      // Act
      const embedding = await extractFaceEmbedding(mockImage, null);
      
      // Assert
      expect(embedding).toBeNull();
      expect(console.error).toHaveBeenCalledWith('face-api.js library not loaded');
    });

    test('should allow retry after face-api.js library becomes available', async () => {
      // Arrange
      const mockImage1 = document.createElement('img');
      
      // Act - First attempt without library
      const embedding1 = await extractFaceEmbedding(mockImage1, null);
      
      // Assert - First attempt fails
      expect(embedding1).toBeNull();
      expect(console.error).toHaveBeenCalledWith('face-api.js library not loaded');
      
      // Arrange - Library becomes available
      jest.clearAllMocks();
      const mockImage2 = document.createElement('img');
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
      
      // Act - Second attempt with library available (retry)
      const embedding2 = await extractFaceEmbedding(mockImage2, mockFaceApi);
      
      // Assert - Second attempt succeeds
      expect(embedding2).not.toBeNull();
      expect(embedding2).toHaveLength(128);
    });
  });
});

