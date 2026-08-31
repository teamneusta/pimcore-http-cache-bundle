# Architecture

## Overview

Symfony bundle for Pimcore that adds HTTP cache tagging and invalidation. Bridges Pimcore's element lifecycle to FOSHttpCacheBundle via adapters, decorators, and events.

- Service definitions live in `config/services.php` (PHP-based DI, not YAML)
- Core interfaces are single-method: `CacheInvalidator::invalidate(CacheTags)` and `ResponseTagger::tag(CacheTags)`

## Decorator Chains

Both `ResponseTagger` and `CacheInvalidator` are built as decoration chains. Lower priority = outer decorator (executes first).

**ResponseTagger chain:**
```
CacheTagCollectionResponseTagger  (priority +1, removed if profiler absent)
  → OnlyWhenActiveResponseTagger  (priority -100, checks CacheActivator)
    → RemoveDisabledTagsResponseTagger  (priority -99, filters via CacheTagChecker)
      → ResponseTaggerAdapter  (base, bridges to FOSHttpCache)
```

**CacheInvalidator chain:**
```
OnlyWhenActiveCacheInvalidator  (priority -100, checks CacheActivator)
  → RemoveDisabledTagsCacheInvalidator  (priority -99, filters via CacheTagChecker)
    → CacheInvalidatorAdapter  (base, bridges to FOSHttpCache CacheManager)
```

Each decorator has a single responsibility: activation check, tag filtering, or profiler collection.

## Adapter Layer

`ResponseTaggerAdapter` and `CacheInvalidatorAdapter` sit at the bottom of the decoration chains. They convert the bundle's `CacheTags` value object to string arrays and delegate to FOSHttpCacheBundle (`FosResponseTagger::addTags()` and `CacheManager::invalidateTags()`). This isolates the FOSHttpCache dependency to these two classes.

## Element Lifecycle Flows

**Tagging (element load → response tagged):**
1. Pimcore fires `POST_LOAD` event (asset/document/object)
2. `TagElementListener` receives it, creates `ElementTaggingEvent`, dispatches it
3. Application listeners can add tags or set `cancel = true`
4. If not cancelled, calls `ResponseTagger::tag()` → decoration chain → FOSHttpCache

**Invalidation (element save/delete → cache purged):**
1. Pimcore fires `POST_UPDATE` or `PRE_DELETE` event
2. `InvalidateElementListener` receives it (skips `saveVersionOnly`/`autoSave`)
3. Creates `ElementInvalidationEvent`, dispatches it
4. Application listeners can add related tags or cancel
5. If not cancelled, calls `CacheInvalidator::invalidate()` → decoration chain → FOSHttpCache

Listeners are registered dynamically — only for enabled element types (configured in YAML).

## CacheTagChecker Composition

Checkers decide if a tag is enabled based on configuration. They compose via constructor injection, not decoration:

```
ElementCacheTagChecker
  ├── inner: StaticCacheTagChecker  (handles custom/empty types via cache_types config)
  ├── asset: AssetCacheTagChecker   (loads asset, checks type against config)
  ├── document: DocumentCacheTagChecker  (loads document, checks type against config)
  └── object: ObjectCacheTagChecker  (loads object, checks type + class against config)
```

`ElementCacheTagChecker` routes element tags to the right specific checker via `match` on `ElementType`. Non-element tags fall through to `StaticCacheTagChecker`.

Each element checker: loads the element by ID from `ElementRepository` → checks if the element's type (and class for objects) is enabled in config → defaults to `true` if not explicitly configured.

## CacheType Hierarchy

`CacheType` interface defines how a tag string is formatted:

| Type | `applyTo("42")` | `toString()` | `isEmpty()` | When used |
|------|-----------------|--------------|-------------|-----------|
| `ElementCacheType(Asset)` | `"a42"` | `"a"` | `false` | Pimcore elements |
| `ElementCacheType(Document)` | `"d42"` | `"d"` | `false` | Pimcore elements |
| `ElementCacheType(Object)` | `"o42"` | `"o"` | `false` | Pimcore elements |
| `CustomCacheType("product")` | `"product-42"` | `"product"` | `false` | User-defined types |
| `EmptyCacheType` | `"42"` | `""` | `true` | Raw string tags |

Note: Element types use direct concatenation (`a42`), custom types use dash separator (`product-42`).

`CacheTypeFactory` creates the right type: `createFromElement()` → `ElementCacheType`, `createFromString("asset")` → `ElementCacheType`, `createFromString("product")` → `CustomCacheType`, `createEmpty()` → `EmptyCacheType`.

Reserved prefixes `a`, `d`, `o` cannot be used for custom types (`ElementCacheType::isReserved()`).

## Immutable Value Objects

- **`CacheTag`**: private constructor, created via `fromString()` / `fromElement()`. Holds `tag` string + `CacheType`. `toString()` delegates to `$type->applyTo($tag)`.
- **`CacheTags`**: immutable collection indexed by tag string. `with()` returns new instance with merged tags. `withoutDisabled($checker)` returns new instance with filtered tags. Implements `IteratorAggregate`.

## Configuration Flow

```
YAML config
  → Configuration tree builder (normalizes defaults: folder=false, email=false, hardlink=false)
    → NeustaPimcoreHttpCacheExtension::loadInternal()
      → Injects cache_types into StaticCacheTagChecker
      → Injects element config into AssetCacheTagChecker / DocumentCacheTagChecker / ObjectCacheTagChecker
      → Dynamically adds event listener tags to TagElementListener / InvalidateElementListener
      → Stores full config as container parameter for DataCollector
```

Key: if an element type is disabled in config, its event listeners are never registered — zero overhead.

## Compiler Pass

`DisableDataCollectorPass`: if the Symfony profiler service is absent, removes `CacheTagCollectionResponseTagger` and `DataCollector`. This avoids the memory overhead of collecting tags in production where the profiler isn't available.

## Design Patterns Summary

| Pattern | Where | Why |
|---------|-------|-----|
| Decorator | ResponseTagger + CacheInvalidator chains | Composable, ordered processing with single responsibility per layer |
| Adapter | ResponseTaggerAdapter, CacheInvalidatorAdapter | Isolates FOSHttpCache dependency |
| Strategy | CacheType hierarchy | Different tag formatting per type |
| Chain of Responsibility | CacheTagChecker composition | Each checker handles its own tag types |
| Event-Driven | ElementTaggingEvent, ElementInvalidationEvent | Loose coupling, extensibility for application code |
| Immutable Value Object | CacheTags, CacheTag | Prevents accidental mutation, thread safety |
| Named Constructor | CacheTag::fromString(), CacheTags::fromElement() | Expressive, validated object creation |
| Named Exception Factory | InvalidArgumentException::becauseCacheTagIsEmpty() | Readable, centralized error messages |
