# solid-single-responsibility — One reason to change

**Priority**: HIGH

A class should have only one reason to change — it should do one thing and do it well.

## Rule

Each class should encapsulate a single responsibility. If you can describe what a class does using "and", it likely has too many responsibilities.

## Bad

```php
<?php declare(strict_types=1);

final class CacheManager
{
    public function tagResponse(Response $response, array $tags): void
    {
        // Tagging logic
    }

    public function invalidateCache(array $tags): void
    {
        // Invalidation logic
    }

    public function checkIfTagEnabled(string $tag): bool
    {
        // Configuration checking logic
    }

    public function collectTagsForProfiler(array $tags): void
    {
        // Profiler data collection
    }
}
```

This class tags responses AND invalidates cache AND checks configuration AND collects profiler data.

## Good

Separate into focused classes:

```php
<?php declare(strict_types=1);

// Single responsibility: tag responses
interface ResponseTagger
{
    public function tag(CacheTags $tags): void;
}

// Single responsibility: invalidate cache
interface CacheInvalidator
{
    public function invalidate(CacheTags $tags): void;
}

// Single responsibility: check if tags are enabled
interface CacheTagChecker
{
    public function isEnabled(CacheTag $tag): bool;
}
```

Then compose via decoration:

```php
// Each decorator has ONE responsibility
final class OnlyWhenActiveResponseTagger implements ResponseTagger
{
    public function __construct(
        private readonly ResponseTagger $inner,
        private readonly CacheActivator $cacheActivator,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        if ($this->cacheActivator->isCachingActive()) {
            $this->inner->tag($tags);
        }
    }
}
```

## Signs of Violation

- Class has many unrelated methods
- Constructor has too many dependencies (> 4-5)
- Class name includes "Manager", "Handler", "Processor" (vague catch-all names)
- Changes to one feature require modifying the same class as another feature

## How to Fix

1. Identify distinct responsibilities
2. Extract each into its own class with a focused interface
3. Compose via dependency injection or decoration
