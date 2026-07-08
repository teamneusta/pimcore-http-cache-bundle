## Cache Scope

The cache scope controls whether and from which point in the request lifecycle cache tags are collected.
Tags are only collected while the scope is **active**. If the scope is inactive, all tagging and invalidation is skipped.

### Automatic activation

The bundle activates the scope automatically in two contexts:

- **HTTP requests**: The scope is activated for every cacheable request (`GET` or `HEAD`, non-admin context).
  When it is activated depends on the [`scope` configuration option](#scope-option).
- **Console commands**: The scope is activated at the start of every console command.

### Scope option

The `scope` option controls at which point in the request lifecycle the scope is activated for HTTP requests:

- `controller` (default): The scope is activated during the `kernel.controller` event.
  Elements loaded earlier — e.g. in `kernel.request` event listeners — are **not** tagged.
- `request`: The scope is activated during the `kernel.request` event, i.e. from the very beginning of the request.
  Use this if you need to tag elements that are loaded in `kernel.request` listeners.

```yaml
neusta_pimcore_http_cache:
    scope: controller  # or "request"
```

### Manual access

You can inject `CacheScope` directly to check or modify the scope state at runtime:

```php
use Neusta\Pimcore\HttpCacheBundle\CacheScope;

final class MyService
{
    public function __construct(
        private readonly CacheScope $cacheScope,
    ) {
    }

    public function doSomething(): void
    {
        if ($this->cacheScope->isActive()) {
            // cache tags are currently being collected
        }
    }
}
```

To **disable** cache tag collection for the current request (e.g. in tests), see [Disabling caching behavior](9-disable-caching-behavior.md).
