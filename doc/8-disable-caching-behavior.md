## Disable caching behavior

You may encounter situations where you need to disable the default caching behavior for specific requests.
This is especially useful for testing purposes.
To achieve this, you can use the `CacheActivator` to disable tagging and invalidation behavior entirely.

### Example for disabling caching behavior in a specific test case
```php

     /** @test */
     public function my_awesome_test_case(): void
    {
        // Disable the caching behavior
        self::getContainer()->get(CacheActivator::class)->deactivateCaching();

        // Your test code here

        self::assertSame('this is amazing!', $result);
    }
```

## Suppress automatic tagging for a specific code block

Sometimes you need to load Pimcore elements for business logic without tagging the current response with those elements. For example, loading related products to calculate a price should not cause the response to be tagged with all those products.

Use `CacheActivator::withoutAutomaticTagging()` to run a block of code with automatic tagging suppressed:

```php
$result = $cacheActivator->withoutAutomaticTagging(function () use ($id) {
    // Automatic tagging is disabled here — loading this element
    // will not tag the current response.
    return $this->repository->find($id);
});
```

### Selectively tagging within the block

If you still want to tag the response with specific tags inside the block, use a generator and `yield` the tags you need:

```php
$result = $cacheActivator->withoutAutomaticTagging(function () use ($id) {
    $element = $this->repository->find($id);

    // Yield only the tags you explicitly want applied to the response.
    yield CacheTag::fromElement($element);

    return $element;
});
```

You can yield both `CacheTag` and `CacheTags` objects. All yielded tags are collected and applied to the response after the block completes. The return value of the generator is returned by `withoutAutomaticTagging()`.

### State restoration

The previous caching state is always restored after the block completes, even if an exception is thrown. If caching was already disabled before calling `withoutAutomaticTagging()`, it remains disabled afterwards.

