# modern-readonly-properties — Use readonly for immutable data

**Priority**: CRITICAL

Use PHP 8.1+ `readonly` keyword to enforce immutability at the language level.

## Rule

Mark properties as `readonly` when they should never change after initialization. Combine with constructor promotion for minimal boilerplate.

## Bad

```php
<?php declare(strict_types=1);

final class CacheTag
{
    private string $tag;
    private CacheType $type;

    public function __construct(string $tag, CacheType $type)
    {
        $this->tag = $tag;
        $this->type = $type;
        // Nothing prevents $this->tag = 'other' later
    }
}
```

## Good

```php
<?php declare(strict_types=1);

final class CacheTag
{
    private function __construct(
        public readonly string $tag,
        public readonly CacheType $type,
    ) {
    }
}
```

## When to Use readonly

- **Always** for constructor-promoted service dependencies:
  ```php
  public function __construct(
      private readonly ResponseTagger $inner,
      private readonly CacheActivator $activator,
  ) {
  }
  ```
- **Always** for value object properties
- **Always** for event data that shouldn't change:
  ```php
  public readonly ElementInterface $element,
  public readonly ElementType $elementType,
  ```

## When NOT to Use readonly

- Properties that intentionally change state:
  ```php
  // CacheActivator needs mutable state
  private bool $isCachingActive = true;

  // Event cancellation flag must be writable
  public bool $cancel = false;
  ```

## readonly + Promoted = Best Practice

```php
// Maximally concise and safe
public function __construct(
    private readonly CacheInvalidator $invalidator,
    private readonly EventDispatcherInterface $dispatcher,
) {
}
```

No separate property declarations, no manual assignment, no accidental reassignment.
