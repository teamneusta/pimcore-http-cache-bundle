# Changelog

## Version 0.8.0

- feature: Introduced `CacheScope` service for controlling when and whether cache tags are collected
- feature: New `scope` configuration option (`controller` / `request`) to control when tag collection begins within a request
- feature: New `tag_fallback_document` configuration option to tag responses with fallback documents
- deprecation: `CacheActivator` is deprecated; use `CacheScope` instead
- deprecation: `CacheActivator::isCachingActive()` is deprecated; use `CacheScope::isActive()` instead
- deprecation: `CacheActivator::activateCaching()` is deprecated; use `CacheScope::enable()` instead
- deprecation: `CacheActivator::deactivateCaching()` is deprecated; use `CacheScope::disable()` instead

## Version 0.7.0

- feature: Initial Release
