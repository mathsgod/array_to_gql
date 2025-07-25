# Array to GraphQL

A PHP library for converting PHP arrays to GraphQL query syntax.

## Features

- 🔧 **Basic Queries**: Support for simple field selection
- 📊 **Nested Queries**: Support for multi-level nested array structures
- 🎯 **Parameterized Queries**: Use `__args` to add query parameters
- 🏷️ **Alias Queries**: Use `__aliasFor` to create GraphQL aliases
- 🎨 **Object Parameters**: Support for complex nested object parameters
- ✅ **Boolean Fields**: `true` values display as field selection, `false` values are ignored
- 🎯 **Value Processing**: All non-`false` values (strings, numbers, `true`) only display key names, not values
- 🔒 **Character Escaping**: Automatic handling of special character escaping

## Installation

```bash
composer install
```

## Usage

### Basic Usage

```php
require 'function.php';

// Simple query - all non-false values only show key names
$result = array_to_gql([
    'user' => [
        'id' => 1,           // Number value → only show key name
        'name' => 'John',    // String value → only show key name
        'email' => true,     // true value → show key name
        'phone' => false     // false value → ignored
    ]
]);
// Output: user { id name email }
```

### Value Processing Rules

```php
// Value processing rules example
$result = array_to_gql([
    'users' => [
        'name' => [
            'first' => 'John',    // String → only show first
            'last' => 'Doe'       // String → only show last
        ],
        'age' => 25,              // Number → only show age
        'active' => true,         // true → show active
        'deleted' => false,       // false → completely ignored
        'status' => 'online'      // String → only show status
    ]
]);
// Output: users { name { first last } age active status }
```

### Parameterized Queries

```php
// Simple parameters
$result = array_to_gql([
    'users' => [
        '__args' => [
            'limit' => 10
        ],
        'id' => true,
        'name' => true
    ]
]);
// Output: users(limit: "10") { id name }

// Object parameters
$result = array_to_gql([
    'users' => [
        '__args' => [
            'search' => [
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]
        ],
        'id' => true,
        'name' => true
    ]
]);
// Output: users(search: {first_name: "John", last_name: "Doe"}) { id name }
```

### Alias Queries

```php
$result = array_to_gql([
    'allUsers' => [
        '__aliasFor' => 'users',
        '__args' => [
            'status' => 'active'
        ],
        'id' => true,
        'name' => true
    ]
]);
// Output: allUsers: users(status: "active") { id name }
```

### Complex Nested Queries

```php
$result = array_to_gql([
    'posts' => [
        '__args' => [
            'limit' => 10
        ],
        'id' => true,
        'title' => true,
        'author' => [
            'name' => true,
            'profile' => [
                'bio' => true,
                'avatar' => true
            ]
        ]
    ]
]);
// Output: posts(limit: "10") { id title author { name profile { bio avatar } } }
```

## Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/ArrayToGqlTest.php

# Run tests with detailed output
vendor/bin/phpunit --testdox
```

## Test Coverage

Current tests cover:

- ✅ Basic field selection
- ✅ Nested array structures
- ✅ Boolean value fields
- ✅ Simple and complex parameters
- ✅ Object parameters (multi-level nesting)
- ✅ Alias functionality
- ✅ Alias with parameter combinations
- ✅ Special character escaping
- ✅ Empty array handling
- ✅ Mixed value types

## API Reference

### `array_to_gql($array): string`

Converts a PHP array to a GraphQL query string.

#### Special Keys

- `__args`: Define query parameters
- `__aliasFor`: Define field alias

#### Parameters

- `$array` (array): The PHP array to convert

#### Return Value

- `string`: GraphQL query string

## License

MIT License
