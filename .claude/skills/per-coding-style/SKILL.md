---
name: per-coding-style
description: This skill should be used when the user asks to "check PER coding style", "review code against PER", "apply PER Coding Style 3.0", "audit PHP formatting", "enforce PER style", or references the PHP-FIG PER Coding Style standard. Validates PHP code against the PER Coding Style 3.0 specification — the successor to PSR-12.
allowed-tools: Read, Grep, Glob
argument-hint: "[file-or-directory]"
---

# PER Coding Style 3.0

PER Coding Style 3.0 is the PHP-FIG standard for PHP code formatting. It extends and supersedes PSR-12.

## When to Apply

Use this skill to audit or review PHP files for compliance with PER Coding Style 3.0:
- Code reviews targeting formatting consistency
- CI/CD pre-merge checks
- Onboarding new contributors to project style
- Migrating from PSR-12

## Rule Categories

| Category | Key Rules |
|---|---|
| Files | LF endings, no `?>` close tag, 80/120 char lines |
| Indentation | 4 spaces, no tabs |
| Types | lowercase keywords, short forms, `?T` nullables |
| Trailing commas | Required on multi-line, forbidden on single-line |
| Modifiers | Fixed keyword order (inheritance → visibility → set-visibility → scope → mutation) |
| Control structures | Braces always, `elseif` not `else if`, operators at line start on wrap |
| Closures | Space after `function`, `fn` without space |
| Arrays | `[]` syntax, trailing comma on multi-line |
| Attributes | No space after `#[`, own line before structure |
| Enums | PascalCase cases, backed type with no space before colon |
| Heredoc/Nowdoc | Prefer nowdoc; declaration on same line as usage |

## Critical Rules (Quick Reference)

**Modifier keyword order** (most frequently wrong):
```php
// CORRECT order: abstract/final → public/protected/private → public(set)/protected(set)/private(set) → static → readonly
abstract protected static readonly string $name;
final public private(set) int $count = 0;
```

**Trailing commas** (required multi-line, forbidden single-line):
```php
// single-line: no trailing comma
foo($a, $b, $c);

// multi-line: trailing comma required
foo(
    $a,
    $b,
    $c,  // ← required
);
```

**Compound types** — no spaces around `|` or `&`:
```php
// CORRECT
function foo(int|string $a, Countable&Iterator $b): int|null {}

// multi-line: operator at line start
function foo(
    int
    |string
    |null $a,
): void {}
```

**`elseif`** not `else if`:
```php
// CORRECT
if ($a) {
} elseif ($b) {
} else {
}
```

**Arrays** — short syntax, trailing comma on multi-line:
```php
// CORRECT
$arr = ['a', 'b', 'c'];

$arr = [
    'a',
    'b',
    'c',  // ← required
];
```

**Closures vs arrow functions**:
```php
// closure: space after `function`, space before/after `use`
$fn = function (int $x) use ($y): int {
    return $x + $y;
};

// arrow function: `fn` without space before `(`
$fn = fn (int $x) => $x + $y;
```

**Attributes**:
```php
// own line before class/method/property; no space after `#[`
#[Attribute]
#[Route('/path', methods: ['GET'])]
class Foo {}

// inline for single-line parameter lists
function foo(#[SomeAttr] string $bar): void {}
```

**Enumerations**:
```php
enum Status: string  // no space before colon; space before type
{
    case Active = 'active';    // PascalCase
    case Inactive = 'inactive';
}
```

## Audit Output Format

When auditing code, report findings as:

```
file:line [rule] Description
```

Examples:
```
src/Foo.php:12 [modifier-order] `static` must come after visibility: use `public static` not `static public`
src/Bar.php:34 [trailing-comma] Multi-line array is missing trailing comma after last element
src/Baz.php:56 [elseif] Use `elseif` instead of `else if`
src/Qux.php:78 [array-syntax] Use short array syntax `[]` instead of `array()`
```

## How to Use

Audit a single file: `/per-coding-style src/Element/Foo.php`

Audit a directory: `/per-coding-style src/`

For the complete set of rules with all examples, see `references/full-spec.md`.
