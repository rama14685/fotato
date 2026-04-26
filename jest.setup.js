/**
 * Jest setup file
 * Runs before each test suite
 */

import { jest } from '@jest/globals';

// Mock console methods to reduce noise in test output
global.console = {
  ...console,
  log: jest.fn(),
  error: jest.fn(),
  warn: jest.fn(),
  info: jest.fn(),
  debug: jest.fn(),
};

// Mock alert for browser environment
global.alert = jest.fn();
