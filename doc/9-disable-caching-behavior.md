## Disable caching behavior

You may encounter situations where you need to disable the default caching behavior for specific requests.
This is especially useful for testing purposes.
To achieve this, you can use the `CacheScope` to disable tagging and invalidation behavior entirely.

> [!IMPORTANT]
> Once disabled, the cache scope cannot be re-enabled during the same request or command.
> Calling `enable()` afterward has no effect; the disabled state is only cleared when the scope is reset.

### Example for disabling caching behavior in a specific test case
```php

     /** @test */
     public function my_awesome_test_case(): void
    {
        // Disable the caching behavior
        self::getContainer()->get(CacheScope::class)->disable();

        // Your test code here
        
        self::assertSame('this is amazing!', $result);
    }
```
