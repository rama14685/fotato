/**
 * Mock implementation of face-api.js for testing
 */

// Mock model loading functions
const createMockNet = (name) => ({
  loadFromUri: jest.fn().mockResolvedValue(undefined),
  _name: name
});

export const nets = {
  ssdMobilenetv1: createMockNet('ssdMobilenetv1'),
  faceLandmark68Net: createMockNet('faceLandmark68Net'),
  faceRecognitionNet: createMockNet('faceRecognitionNet')
};

export const detectSingleFace = jest.fn();
export const withFaceLandmarks = jest.fn();
export const withFaceDescriptor = jest.fn();

// Default export for CommonJS compatibility
export default {
  nets,
  detectSingleFace,
  withFaceLandmarks,
  withFaceDescriptor
};
