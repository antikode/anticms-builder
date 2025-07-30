#!/usr/bin/env bash

# AntiCmsBuilder Package Test Runner
# This script runs the test suite for the AntiCmsBuilder package

set -e

echo "🧪 Running AntiCmsBuilder Package Tests..."
echo "======================================"

# Check if we're in the right directory
if [ ! -f "phpunit.xml" ]; then
    echo "❌ Error: phpunit.xml not found. Please run this script from the package root directory."
    exit 1
fi

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Run the tests
echo "🏃 Running tests..."
./vendor/bin/phpunit --testdox

echo ""
echo "✅ Tests completed!"

# Optional: Run with coverage if --coverage flag is passed
if [ "$1" = "--coverage" ]; then
    echo ""
    echo "📊 Generating code coverage report..."
    ./vendor/bin/phpunit --coverage-html coverage-report
    echo "📊 Coverage report generated in coverage-report/ directory"
fi