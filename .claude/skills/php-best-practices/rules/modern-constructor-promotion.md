# modern-constructor-promotion — Use constructor property promotion

**Priority**: CRITICAL

Use PHP 8.0+ constructor property promotion to reduce boilerplate.

## Rule

Declare and assign properties directly in the constructor signature instead of separate property declarations + manual assignment.

## Bad

```php
<?php declare(strict_types=1);

final class UserService
{
    private UserRepository $repository;
    private LoggerInterface $logger;

    public function __construct(
        UserRepository $repository,
        LoggerInterface $logger,
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }
}
```

## Good

```php
<?php declare(strict_types=1);

final class UserService
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly LoggerInterface $logger,
    ) {
    }
}
```

## When Not to Promote

- When the property needs transformation before assignment:
  ```php
  public function __construct(CacheTag ...$tags)
  {
      // Needs indexing — can't promote
      $this->tags = array_combine(
          array_map(fn (CacheTag $t) => $t->toString(), $tags),
          $tags,
      );
  }
  ```

## Combine with readonly

Always pair promotion with `readonly` for immutable dependencies:

```php
public function __construct(
    private readonly CacheInvalidator $invalidator,
    private readonly EventDispatcherInterface $dispatcher,
) {
}
```

## Trailing Comma

Always use a trailing comma after the last parameter in multiline constructors.
