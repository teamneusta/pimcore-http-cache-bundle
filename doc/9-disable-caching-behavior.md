## Disable caching behavior

You may encounter situations where you need to disable the default caching behavior for specific requests or commands.
This is especially useful for testing purposes.

To achieve this, you can use `CacheScope::disable()` to disable tagging and invalidation behavior entirely
for the current request or command.

> [!IMPORTANT]
> Once disabled, the cache scope cannot be re-enabled by calling `startCollecting()` or `withCollecting()` during the same request or command.
> The disabled state is only cleared when the scope is reset.

### Example for disabling caching behavior in a specific test case

```php
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Pimcore\Test\KernelTestCase;

final class MyAwesomeTest extends KernelTestCase
{
    /** @test */ 
    public function my_awesome_test_case(): void
    {
        self::getContainer()->get(CacheScope::class)->disable();

        // Your test code here
        
        self::assertSame('this is amazing!', $result);
    }
}
```

### Difference to temporarily pausing collection or invalidation

`disable()` disables cache-related behavior for the whole request or command.
If you only want to temporarily pause or resume tag collection or invalidation for a specific block of code, use the
cache scope methods described in [Cache Scope](8-cache-scope.md#temporarily-pausing-collection) instead.
