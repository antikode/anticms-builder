# AntiCmsBuilder Package Tests

This directory contains comprehensive unit and feature tests for the AntiCmsBuilder package.

## Test Structure

```
tests/
├── Feature/           # Integration tests
│   └── UseCrudControllerTest.php
├── Unit/              # Unit tests
│   ├── FieldServiceTest.php
│   ├── FormBuilderTest.php
│   ├── ServiceProviderTest.php
│   ├── TableBuilderTest.php
│   └── FieldTypes/
│       ├── FieldTypeTest.php
│       ├── InputFieldTest.php
│       └── SelectFieldTest.php
├── Support/           # Test utilities and mocks
│   ├── TestController.php
│   └── TestModel.php
├── TestCase.php       # Base test case
└── README.md         # This file
```

## Running Tests

### Prerequisites
- PHP 8.2+
- Composer
- PHPUnit 10.0+

### Quick Start
```bash
# From the package root directory
./run-tests.sh
```

### Manual Test Execution
```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/phpunit

# Run with test descriptions
./vendor/bin/phpunit --testdox

# Run specific test files
./vendor/bin/phpunit tests/Unit/FieldServiceTest.php

# Run with coverage (requires Xdebug)
./vendor/bin/phpunit --coverage-html coverage-report
```

## Test Coverage

The test suite covers:

### Core Components
- ✅ **FieldService**: Template form field processing and multilanguage support
- ✅ **FormBuilder**: Form building, validation, and relationship handling
- ✅ **TableBuilder**: Table creation, filtering, and query building
- ✅ **ServiceProvider**: Package registration and service binding

### Field Types
- ✅ **FieldType** (Base class): Common field functionality and fluent interface
- ✅ **InputField**: Input field specific features (max/min, type setting)
- ✅ **SelectField**: Option handling and select-specific features

### Traits
- ✅ **UseCrudController**: CRUD operations, permissions, and auto-relationship detection

## Test Categories

### Unit Tests
Focus on testing individual components in isolation:
- Method behavior and return values
- Property setting and getting
- Validation and error handling
- Edge cases and boundary conditions

### Feature Tests
Test component interactions and integration:
- Controller trait functionality
- End-to-end form processing
- Permission system integration
- Database relationship loading

## Writing New Tests

### Adding a Unit Test
```php
<?php

namespace AntiCmsBuilder\Tests\Unit;

use AntiCmsBuilder\Tests\TestCase;

class NewComponentTest extends TestCase
{
    public function test_component_functionality()
    {
        // Test implementation
        $this->assertTrue(true);
    }
}
```

### Adding a Feature Test
```php
<?php

namespace AntiCmsBuilder\Tests\Feature;

use AntiCmsBuilder\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewFeatureTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_feature_integration()
    {
        // Feature test implementation
        $this->assertTrue(true);
    }
}
```

## Test Utilities

### TestCase
Base test case providing:
- Laravel Testbench integration
- Package service provider registration
- SQLite in-memory database setup

### TestModel
Mock Eloquent model for testing:
- Implements `HasCustomField` contract
- Provides mock custom fields data
- Safe for database-less testing

### TestController
Mock controller implementing `UseCrudController`:
- Demonstrates trait usage
- Provides test implementations of required methods
- Used for feature testing

## Best Practices

1. **Isolation**: Unit tests should not depend on external services
2. **Mocking**: Use Mockery for external dependencies
3. **Descriptive Names**: Test method names should describe the behavior being tested
4. **Arrange-Act-Assert**: Structure tests clearly with setup, action, and verification
5. **Edge Cases**: Test boundary conditions and error scenarios
6. **Coverage**: Aim for high code coverage while focusing on meaningful tests

## Continuous Integration

Tests are designed to run in CI environments:
- No external dependencies
- SQLite in-memory database
- Configurable test execution
- Coverage reporting support

## Troubleshooting

### Common Issues

**Tests not found**: Ensure autoload-dev is properly configured in composer.json
```bash
composer dump-autoload
```

**Memory issues**: Increase PHP memory limit
```bash
php -d memory_limit=512M vendor/bin/phpunit
```

**Database errors**: Ensure SQLite extension is installed
```bash
php -m | grep sqlite
```

## Contributing

When adding new features to the package:
1. Write tests first (TDD approach)
2. Ensure all existing tests pass
3. Add tests for new functionality
4. Update test documentation as needed
5. Maintain high test coverage

For questions or issues with tests, please refer to the main package documentation or create an issue in the project repository.