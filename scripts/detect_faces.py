#!/usr/bin/env python3
"""
Face Detection Script for Fotlist
Detects faces in photos and extracts 128-dimensional embeddings
Requires: face_recognition library (pip install face_recognition)
"""

import sys
import json
import face_recognition
import numpy as np

def detect_faces(image_path):
    """
    Detect faces in an image and return their embeddings
    
    Args:
        image_path: Path to the image file
        
    Returns:
        dict: JSON object with embeddings array
    """
    try:
        # Load image
        image = face_recognition.load_image_file(image_path)
        
        # Detect face locations
        face_locations = face_recognition.face_locations(image, model="hog")
        
        if not face_locations:
            return {"embeddings": [], "face_count": 0}
        
        # Get face encodings (128-dimensional embeddings)
        face_encodings = face_recognition.face_encodings(image, face_locations)
        
        # Convert numpy arrays to lists for JSON serialization
        embeddings = [encoding.tolist() for encoding in face_encodings]
        
        return {
            "embeddings": embeddings,
            "face_count": len(embeddings),
            "face_locations": face_locations
        }
        
    except Exception as e:
        return {
            "error": str(e),
            "embeddings": [],
            "face_count": 0
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No image path provided", "embeddings": []}))
        sys.exit(1)
    
    image_path = sys.argv[1]
    result = detect_faces(image_path)
    print(json.dumps(result))
