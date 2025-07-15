# Filterer Test Suite Documentation

This document explains the modular test structure of the Filterer package and provides guidelines for maintaining and extending the test suite.

## Test Structure Overview

The test suite is organized following the **Single Responsibility Principle**, with each test file focusing on a specific aspect of the Filterer functionality:

```
tests/
├── FiltererTest.php              # Core trait functionality & basic integration
├── FilterablePaginationTest.php  # All pagination-related tests
├── FilterableValidationTest.php  # All validation-related tests
├── FilterableFilteringTest.php   # Complex filtering scenarios
├── FilterableSortingTest.php     # Complex sorting scenarios
├── TestCase.php                  # Base test class
├── fixtures/                     # Test fixtures and models
└── database/                     # Test database setup
```

## Test File Responsibilities

### FiltererTest.php (Core Functionality)
**Purpose**: Tests core trait functionality and basic integration
**Test Count**: 5 tests
**Responsibilities**:
- Verifies trait methods are added to models (`scopeFilter`, `scopeFilterPaginate`)
- Tests basic return types (Builder, LengthAwarePaginator)
- Validates empty parameter handling
- Confirms method chaining works correctly

### FilterablePaginationTest.php (Pagination)
**Purpose**: Comprehensive pagination functionality testing
**Test Count**: 16 tests
**Responsibilities**:
- Explicit page parameter handling (cookie persistence scenarios)
- Laravel automatic page discovery and backward compatibility
- Edge cases (large pages, empty results, mixed parameters)
- Integration with filtering and sorting

### FilterableValidationTest.php (Validation)
**Purpose**: All validation logic testing
**Test Count**: 17 tests
**Responsibilities**:
- Filter field validation (existence, operators)
- Sort field validation (existence, directions)
- Pagination parameter validation (types, ranges)
- Combined validation scenarios
- Positive validation (ensuring valid parameters work)

### FilterableFilteringTest.php (Filtering)
**Purpose**: Complex filtering scenarios and edge cases
**Test Count**: 20 tests
**Responsibilities**:
- Numeric, string, date/datetime field filtering
- All operators (equal_to, contains, between, etc.)
- Relational filtering (one-to-many, many-to-one, many-to-many)
- Complex scenarios (multiple filters, custom filters)

### FilterableSortingTest.php (Sorting)
**Purpose**: Complex sorting scenarios
**Test Count**: 9 tests
**Responsibilities**:
- Basic field sorting (ascending/descending)
- Relational sorting (all relationship types)
- Multi-field sorting with different directions
- Complex sorting combinations

## Test Patterns and Conventions

### Naming Conventions
- **Test files**: `Filterable{Functionality}Test.php`
- **Test methods**: `it_{describes_what_is_tested}(): void`
- **Test annotations**: Use `/** @test */` before each test method

### Code Style
- Use `$this->` instead of `self::` for assertions
- Include clear section headers with comment blocks
- Add descriptive comments for complex test scenarios
- Follow PSR-12 coding standards

### Test Organization
Each test file should include:
```php
/*
|--------------------------------------------------------------------------
| Section Name (e.g., Basic Field Sorting)
|--------------------------------------------------------------------------
*/
```

### Data Setup
- Use the `seedDatabase()` method for test-specific data setup
- Create realistic test data that represents actual use cases
- Use factory methods for model creation
- Include relationships when testing relational functionality

## Running Tests

### Full Test Suite
```bash
docker container run --rm -it -v $(pwd):/app -w /app composer:2.4 composer test
```

### Specific Test Files
```bash
# Core functionality only
./vendor/bin/phpunit tests/FiltererTest.php

# Pagination tests only
./vendor/bin/phpunit tests/FilterablePaginationTest.php

# Validation tests only
./vendor/bin/phpunit tests/FilterableValidationTest.php

# Filtering tests only
./vendor/bin/phpunit tests/FilterableFilteringTest.php

# Sorting tests only
./vendor/bin/phpunit tests/FilterableSortingTest.php
```

## Adding New Tests

### When to Add to Existing Files
- **FiltererTest.php**: Only for new core trait methods or basic integration
- **FilterablePaginationTest.php**: New pagination features or edge cases
- **FilterableValidationTest.php**: New validation rules or scenarios
- **FilterableFilteringTest.php**: New operators, field types, or complex filtering
- **FilterableSortingTest.php**: New sorting features or complex scenarios

### When to Create New Files
Consider creating a new test file when:
- Adding a completely new feature area (e.g., caching, performance)
- The functionality doesn't fit clearly into existing categories
- A single test file would become too large (>500 lines)

### Test File Template
```php
<?php

namespace CultureGr\Filterer\Tests;

use CultureGr\Filterer\Tests\Fixtures\Client;
// Add other necessary imports

class FilterableNewFeatureTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Feature Section Name
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function it_describes_what_is_tested(): void
    {
        // Arrange
        factory(Client::class, 5)->create();

        // Act
        $result = Client::filter([/* test parameters */]);

        // Assert
        $this->assertInstanceOf(ExpectedClass::class, $result);
    }

    protected function seedDatabase(): void
    {
        // Set up test-specific data
    }
}
```

## Benefits of This Structure

### For Developers
- **Easy Navigation**: Quickly find tests for specific functionality
- **Focused Testing**: Run only relevant tests during development
- **Clear Responsibilities**: Each file has a single, clear purpose
- **Better Debugging**: Issues are isolated to specific functionality areas

### For Maintenance
- **Scalable**: Easy to add new functionality without bloating existing files
- **Maintainable**: Smaller, focused files are easier to understand and modify
- **Consistent**: Clear patterns and conventions across all test files
- **Comprehensive**: All scenarios are covered without duplication

## Test Coverage Summary

| Test File | Tests | Assertions | Coverage Area |
|-----------|-------|------------|---------------|
| FiltererTest.php | 5 | ~15 | Core trait functionality |
| FilterablePaginationTest.php | 16 | ~45 | Pagination scenarios |
| FilterableValidationTest.php | 17 | ~35 | Validation logic |
| FilterableFilteringTest.php | 20 | ~40 | Filtering scenarios |
| FilterableSortingTest.php | 9 | ~15 | Sorting scenarios |
| **Total** | **67** | **~150** | **Complete coverage** |

This modular structure ensures comprehensive test coverage while maintaining clarity, maintainability, and ease of development.
