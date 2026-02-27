# modern-first-class-callables — Use first-class callable syntax

**Priority**: CRITICAL

Use PHP 8.1+ first-class callable syntax (`Closure::fromCallable` shorthand) for cleaner callback references.

## Rule

Use `$object->method(...)` or `ClassName::method(...)` instead of wrapping in closures or using string-based callables.

## Bad

```php
<?php declare(strict_types=1);

// Wrapping in closure unnecessarily
$tags = array_map(function (ElementInterface $el) {
    return CacheTag::fromElement($el);
}, $elements);

// String-based callable
$filtered = array_filter($tags, [$checker, 'isEnabled']);
```

## Good

```php
<?php declare(strict_types=1);

// First-class callable — static method
$tags = array_map(CacheTag::fromElement(...), $elements);

// First-class callable — instance method
$filtered = array_filter($this->tags, $checker->isEnabled(...));

// Combine with arrow functions only when transformation needed
$strings = array_map(
    static fn (CacheTag $tag): string => $tag->toString(),
    array_values($this->tags),
);
```

## When to Use

- Passing a method as a callback to `array_map`, `array_filter`, `usort`, etc.
- Any place a `callable` or `Closure` is expected
- When the callback is a direct method call with no extra logic

## When Not to Use

Use arrow functions instead when you need to:
- Transform arguments before passing
- Access additional variables from scope
- Apply multiple operations
