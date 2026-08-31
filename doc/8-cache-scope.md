## Cache Scope

The cache scope controls whether and from which point in the request lifecycle cache-related behavior is enabled.

Tagging and invalidation are gated independently:

- **Tagging** only happens while the scope is **enabled**. Tagging only ever makes sense for a request whose response
  can actually be cached, so the scope must be explicitly activated (see [Automatic activation](#automatic-activation)).
- **Invalidation** is active by default — everywhere, including non-cacheable requests such as an admin-UI save or a
  custom `POST` endpoint — and only stops once `disable()` has been called. Invalidation does not depend on the scope
  being enabled.

Calling `disable()` always stops both tagging and invalidation, regardless of the above.

### Automatic activation

The bundle activates the scope automatically in two contexts:

- **HTTP requests**: The scope is activated for every cacheable request (`GET` or `HEAD`, non-admin context).
  When it is activated depends on the [`scope` configuration option](#scope-option).
- **Console commands**: The scope is activated at the start of every console command.

This only affects tagging; invalidation runs regardless of whether the current request or command activates the scope.

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
            // tagging is currently enabled
        }

        if ($this->cacheScope->isInvalidating()) {
            // invalidation is currently active
        }

        $this->cacheScope->disable();
        // both tagging and invalidation are disabled from now on
    }
}
```

To disable cache-related behavior for the current request or command, see
[Disabling caching behavior](9-disable-caching-behavior.md).
