#!/usr/bin/env bash

# AntiCmsBuilder Package Test Runner (Laravel Integration)
# This script runs the package tests within the main Laravel project context

set -e

echo "🧪 Running AntiCmsBuilder Package Tests (Laravel Integration)..."
echo "============================================================="

# Navigate to the main project root
PACKAGE_DIR=$(pwd)
PROJECT_ROOT="../../../.."

if [ ! -f "$PROJECT_ROOT/artisan" ]; then
    echo "❌ Error: Cannot find Laravel project root. Please run this script from the package directory."
    exit 1
fi

cd "$PROJECT_ROOT"

echo "📍 Running from Laravel project: $(pwd)"

# Run package-specific tests using Laravel's test suite
echo "🏃 Running package tests..."

# Filter tests to only run package tests
php artisan test packages/antikode/AntiCmsBuilder/tests --testdox

echo ""
echo "✅ Package tests completed!"

# Return to package directory
cd "$PACKAGE_DIR"