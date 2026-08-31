# Changelog

## Version 0.8.0

- feature: Introduced `CacheScope` service for controlling when and whether cache tags are collected. Cache invalidation is active by default everywhere (including non-cacheable and admin-context requests) and only stops once `CacheScope::disable()` is called; tag collection instead requires the scope to be explicitly enabled for the current request
- feature: New `scope` configuration option (`controller` / `request`) to control when tag collection begins within a request
- feature: New `tag_fallback_document` configuration option to tag responses with fallback documents
- feature: New `CacheScope::withoutCollecting()`/`withCollecting()` methods to temporarily pause/resume tag collection for a block of code, and `isCollecting()` to check the current state
- feature: New `CacheScope::withoutInvalidating()`/`withInvalidating()` methods to temporarily pause/resume cache invalidation for a block of code, e.g. around a bulk import
- deprecation: `CacheActivator` is deprecated; use `CacheScope` instead
- deprecation: `CacheActivator::isCachingActive()` is deprecated; use `CacheScope::isInvalidating()` instead
- deprecation: `CacheActivator::activateCaching()` is deprecated; use `CacheScope::enable()` instead
- deprecation: `CacheActivator::deactivateCaching()` is deprecated; use `CacheScope::disable()` instead

## Version 0.7.0

- feature: Initial Release
