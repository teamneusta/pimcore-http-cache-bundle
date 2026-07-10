## Cache Scope

The cache scope controls whether and from which point in the request lifecycle cache-related behavior is enabled.
Tags are only collected while the scope is **enabled**. If the scope is disabled, all tagging and invalidation is skipped.

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
        if ($this->cacheScope->isEnabled()) {
            // cache-related behavior is currently enabled
        }

        $this->cacheScope->disable();
        // cache-related behavior is disabled from now on
    }
}
```

To disable cache-related behavior for the current request or command, see
[Disabling caching behavior](9-disable-caching-behavior.md).
