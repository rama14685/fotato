/**
 * Face Detection using face-api.js (Node.js version)
 * Alternative to Python script - easier setup
 */

const faceapi = require('face-api.js');
const canvas = require('canvas');
const fs = require('fs');
const path = require('path');

// Setup canvas for Node.js
const { Canvas, Image, ImageData } = canvas;
faceapi.env.monkeyPatch({ Canvas, Image, ImageData });

// Load models
async function loadModels() {
    const MODEL_URL = path.join(__dirname, '../public/models');
    
    await faceapi.nets.tinyFaceDetector.loadFromDisk(MODEL_URL);
    await faceapi.nets.faceLandmark68Net.loadFromDisk(MODEL_URL);
    await faceapi.nets.faceRecognitionNet.loadFromDisk(MODEL_URL);
    
    console.log('Models loaded successfully');
}

// Detect faces in image
async function detectFaces(imagePath) {
    try {
        // Load image
        const img = await canvas.loadImage(imagePath);
        
        // Detect faces
        const detections = await faceapi
            .detectAllFaces(img, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptors();
        
        if (!detections || detections.length === 0) {
            return {
                embeddings: [],
                face_count: 0
            };
        }
        
        // Extract embeddings (128-dimensional descriptors)
        const embeddings = detections.map(d => Array.from(d.descriptor));
        
        return {
            embeddings: embeddings,
            face_count: embeddings.length,
            face_locations: detections.map(d => d.detection.box)
        };
        
    } catch (error) {
        return {
            error: error.message,
            embeddings: [],
            face_count: 0
        };
    }
}

// Main function
async function main() {
    if (process.argv.length < 3) {
        console.log(JSON.stringify({
            error: 'No image path provided',
            embeddings: []
        }));
        process.exit(1);
    }
    
    const imagePath = process.argv[2];
    
    if (!fs.existsSync(imagePath)) {
        console.log(JSON.stringify({
            error: 'Image file not found',
            embeddings: []
        }));
        process.exit(1);
    }
    
    await loadModels();
    const result = await detectFaces(imagePath);
    console.log(JSON.stringify(result));
}

main().catch(error => {
    console.log(JSON.stringify({
        error: error.message,
        embeddings: []
    }));
    process.exit(1);
});
