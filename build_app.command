#!/bin/bash

# Build script for macOS
cd "$(dirname "$0")"
echo "Starting build process..."
npm run build
echo "Build complete!"
