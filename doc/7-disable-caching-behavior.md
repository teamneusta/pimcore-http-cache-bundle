## Disable caching behavior

You may encounter situations where you need to disable the default caching behavior for specific requests.
This is especially useful for testing purposes.
To achieve this, you can use the CacheActivator to disable tagging and invalidation behavior.

### Example for disabling caching behavior in a specific testcase
```php

 public function my_awesome_test_testcase(): void
    {
        // Disable the caching behavior
        self::getContainer()->get(CacheActivator::class)->deactivateCaching();

        // Your test code here
        
        self::assertSame( 'this is amazing!', $result);
    }
```

