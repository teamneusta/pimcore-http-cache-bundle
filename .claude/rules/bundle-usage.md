# Bundle Usage & Concepts

## What This Bundle Does

Adds automatic HTTP cache invalidation to Pimcore via cache tags. When a Pimcore element (document, asset, data object) is loaded during a request, the response gets tagged. When that element changes, the cache is invalidated. Works with reverse proxies (Varnish, Fastly) via FOSHttpCacheBundle.

## Automatic Behavior

**Tagging** — when an element is loaded during a request:
1. Pimcore fires a `POST_LOAD` event
2. Bundle dispatches `ElementTaggingEvent`
3. Response is tagged with `a{id}` (asset), `d{id}` (document), or `o{id}` (object)
4. Tags appear in the `X-Cache-Tags` header

**Invalidation** — when an element is saved or deleted:
1. Pimcore fires `POST_UPDATE` or `PRE_DELETE`
2. Bundle dispatches `ElementInvalidationEvent`
3. Cache tags are invalidated via FOSHttpCacheBundle
4. Reverse proxy purges matching responses
5. Skipped for `saveVersionOnly` and `autoSave` operations

## Configuration

```yaml
neusta_pimcore_http_cache:
    elements:
        assets:
            enabled: true
            types:
                folder: false        # disabled by default
        documents:
            enabled: true
            types:
                email: false         # disabled by default
                folder: false        # disabled by default
                hardlink: false      # disabled by default
        objects:
            enabled: true
            types:
                folder: false        # disabled by default
            classes:
                MyClass: false       # disable specific data object class
    cache_types:
        my_custom_type: true         # must be defined here before use
```

## Public API

Services available via autowiring:

- **`CacheActivator`** — toggle caching on/off programmatically
- **`CacheInvalidator`** — manually invalidate cache tags
- **`ResponseTagger`** — manually tag the current response

Value objects:

- **`CacheTags`** — immutable collection; create via `fromString()`, `fromStrings()`, `fromElement()`, `fromElements()`; combine with `with()`
- **`CacheTag`** — single tag; create via `fromString()`, `fromElement()`
- **`CacheTypeFactory`** — creates `ElementCacheType`, `CustomCacheType`, or `EmptyCacheType`

## Events

Both events share the same API — `element`, `elementType`, `addTag()`, `addTags()`, `cacheTags()`, and `cancel`:

**`ElementTaggingEvent`** — fired before tagging a response:
```php
#[AsEventListener]
final class AddRelatedTags
{
    public function __invoke(ElementTaggingEvent $event): void
    {
        // Add related element tags
        $event->addTags(CacheTags::fromElements($event->element->getRelatedProducts()));

        // Or cancel tagging entirely
        $event->cancel = true;
    }
}
```

**`ElementInvalidationEvent`** — fired before invalidating cache:
```php
#[AsEventListener]
final class InvalidateRelated
{
    public function __invoke(ElementInvalidationEvent $event): void
    {
        // Invalidate related content too
        $event->addTag(CacheTag::fromElement($relatedElement));
    }
}
```

## Custom Cache Types

For grouping tags beyond the built-in element types:

1. Register in config: `cache_types: { product_category: true }`
2. Use in code:
   ```php
   $tag = CacheTag::fromString('42', new CustomCacheType('product_category'));
   // Produces tag: "product_category-42"
   ```
3. Invalidate by type:
   ```php
   $cacheInvalidator->invalidate(
       CacheTags::fromStrings(['42'], new CustomCacheType('product_category'))
   );
   ```

Reserved prefixes `a`, `d`, `o` cannot be used for custom types.

## Three Ways to Disable Caching

1. **Configuration** — permanently disable types/classes in YAML
2. **Events** — set `$event->cancel = true` conditionally
3. **`CacheActivator`** — `deactivateCaching()` disables everything at runtime (useful in tests)

## Profiler

Shows in the Symfony profiler toolbar when the profiler is enabled. Displays all cache tags applied to the response and the current bundle configuration.
