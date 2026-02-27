# solid-interface-segregation — Small, focused interfaces

**Priority**: HIGH

Clients should not be forced to depend on interfaces they don't use.

## Rule

Prefer small, single-method interfaces over large ones. A class needing only one capability shouldn't depend on an interface with ten methods.

## Bad

```php
<?php declare(strict_types=1);

interface CacheService
{
    public function tag(CacheTags $tags): void;
    public function invalidate(CacheTags $tags): void;
    public function isActive(): bool;
    public function isEnabled(CacheTag $tag): bool;
    public function collect(CacheTags $tags): void;
}

// This listener only needs invalidation, but depends on everything
final class InvalidateListener
{
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    public function onUpdate(): void
    {
        $this->cache->invalidate($tags); // Only uses 1 of 5 methods
    }
}
```

## Good

```php
<?php declare(strict_types=1);

interface CacheInvalidator
{
    public function invalidate(CacheTags $tags): void;
}

interface ResponseTagger
{
    public function tag(CacheTags $tags): void;
}

interface CacheTagChecker
{
    public function isEnabled(CacheTag $tag): bool;
}

// Depends only on what it needs
final class InvalidateListener
{
    public function __construct(
        private readonly CacheInvalidator $invalidator,
    ) {
    }

    public function onUpdate(): void
    {
        $this->invalidator->invalidate($tags);
    }
}
```

## Benefits

- Classes depend only on what they use
- Interfaces are easy to implement and mock in tests
- Adding a new capability doesn't force changes on unrelated implementations
- Single-method interfaces compose well with decoration

## Signs of Violation

- An interface has more than 3-4 methods
- Implementations throw `RuntimeException('Not supported')` for some methods
- Test mocks stub many methods that the test doesn't care about
