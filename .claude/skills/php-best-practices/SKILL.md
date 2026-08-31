---
name: php-best-practices
description: Modern PHP 8.x patterns, PSR standards, type system best practices, and SOLID principles. Contains 45+ rules for writing clean, maintainable PHP code.
allowed-tools: Read, Grep, Glob
argument-hint: "[file-or-directory]"
---

# PHP Best Practices

Modern PHP 8.x patterns, PSR standards, type system best practices, and SOLID principles.

## When to Apply

Reference these guidelines when:
- Writing or reviewing PHP code
- Implementing classes and interfaces
- Using PHP 8.x modern features
- Ensuring type safety
- Following PSR standards
- Applying design patterns

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Type System | CRITICAL | `type-` |
| 2 | Modern Features | CRITICAL | `modern-` |
| 3 | PSR Standards | HIGH | `psr-` |
| 4 | SOLID Principles | HIGH | `solid-` |
| 5 | Error Handling | HIGH | `error-` |
| 6 | Performance | MEDIUM | `perf-` |
| 7 | Security | CRITICAL | `sec-` |

## Quick Reference

### 1. Type System (CRITICAL)
- `type-strict-mode` - Declare strict types
- `type-return-types` - Always declare return types
- `type-parameter-types` - Type all parameters
- `type-property-types` - Type class properties
- `type-union-types` - Use union types effectively
- `type-intersection-types` - Use intersection types
- `type-nullable` - Handle nullable types properly
- `type-mixed-avoid` - Avoid mixed type when possible
- `type-void-return` - Use void for no-return methods
- `type-never-return` - Use never for non-returning functions

### 2. Modern Features (CRITICAL)
- `modern-constructor-promotion` - Use constructor property promotion
- `modern-readonly-properties` - Use readonly for immutable data
- `modern-readonly-classes` - Use readonly classes
- `modern-enums` - Use enums instead of constants
- `modern-attributes` - Use attributes for metadata
- `modern-match-expression` - Use match over switch
- `modern-named-arguments` - Use named arguments for clarity
- `modern-nullsafe-operator` - Use nullsafe operator (`?->`)
- `modern-arrow-functions` - Use arrow functions for simple closures
- `modern-first-class-callables` - Use first-class callable syntax

### 3. PSR Standards (HIGH)
- `psr-4-autoloading` - Follow PSR-4 autoloading
- `psr-12-coding-style` - Follow PSR-12 coding style
- `psr-naming-conventions` - Class and method naming
- `psr-file-structure` - One class per file
- `psr-namespace-declaration` - Proper namespace usage

### 4. SOLID Principles (HIGH)
- `solid-single-responsibility` - One reason to change
- `solid-open-closed` - Open for extension, closed for modification
- `solid-liskov-substitution` - Subtypes must be substitutable
- `solid-interface-segregation` - Small, focused interfaces
- `solid-dependency-inversion` - Depend on abstractions

### 5. Error Handling (HIGH)
- `error-custom-exceptions` - Create specific exceptions
- `error-exception-hierarchy` - Proper exception inheritance
- `error-try-catch-specific` - Catch specific exceptions
- `error-finally-cleanup` - Use finally for cleanup
- `error-never-suppress` - Don't suppress errors with @

### 6. Performance (MEDIUM)
- `perf-avoid-globals` - Avoid global variables
- `perf-lazy-loading` - Load resources lazily
- `perf-array-functions` - Use native array functions
- `perf-string-functions` - Use native string functions
- `perf-generators` - Use generators for large datasets

### 7. Security (CRITICAL)
- `sec-input-validation` - Validate all input
- `sec-output-escaping` - Escape output properly
- `sec-password-hashing` - Use password_hash/verify
- `sec-sql-prepared` - Use prepared statements
- `sec-file-uploads` - Validate file uploads

## Key Patterns (Quick Reference)

```php
<?php declare(strict_types=1);

// Constructor promotion + readonly
class User
{
    public function __construct(
        public readonly string $id,
        private string $email,
    ) {
    }
}

// Enums with methods
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}

// Match expression
$result = match ($status) {
    'pending' => 'Waiting',
    'active' => 'Running',
    default => 'Unknown',
};

// Nullsafe operator
$country = $user?->getAddress()?->getCountry();

// Arrow functions
$names = array_map(fn (User $u) => $u->name, $users);
```

## Output Format

When auditing code, output findings in this format:

```
file:line - [category] Description of issue
```

Example:
```
src/Services/UserService.php:15 - [type] Missing return type declaration
src/Models/Order.php:42 - [modern] Use match expression instead of switch
src/Controllers/ApiController.php:28 - [solid] Class has multiple responsibilities
```

## How to Use

Audit a file or directory: `/php-best-practices src/Cache`

For detailed rule explanations, see the rule files in the `rules/` directory.
