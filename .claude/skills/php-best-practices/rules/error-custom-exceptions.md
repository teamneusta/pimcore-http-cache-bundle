# error-custom-exceptions — Create specific exceptions

**Priority**: HIGH

Create domain-specific exception classes with named static factory methods.

## Rule

Don't throw generic `\RuntimeException` or `\InvalidArgumentException` directly. Create custom exception classes with descriptive factory methods that explain *why* the exception was thrown.

## Bad

```php
<?php declare(strict_types=1);

if ('' === trim($tag)) {
    throw new \InvalidArgumentException('Cache tag must not be empty.');
}

if ($this->isReserved($type)) {
    throw new \InvalidArgumentException(
        sprintf('The cache type "%s" is reserved.', $type)
    );
}
```

## Good

```php
<?php declare(strict_types=1);

// Marker interface for all bundle exceptions
interface PimcoreHttpCacheException extends \Throwable
{
}

// Specific exception with named factories
final class InvalidArgumentException extends \InvalidArgumentException implements PimcoreHttpCacheException
{
    public static function becauseCacheTagIsEmpty(): self
    {
        return new self('Cache tag must not be empty.');
    }

    public static function becauseCacheTypeIsReserved(CacheType $type): self
    {
        return new self(\sprintf(
            'The cache type "%s" is reserved for Pimcore elements.',
            $type->toString(),
        ));
    }

    public static function becauseElementHasNoId(): self
    {
        return new self('The given element has no id.');
    }
}

// Usage — reads like a sentence
throw InvalidArgumentException::becauseCacheTagIsEmpty();
throw InvalidArgumentException::becauseCacheTypeIsReserved($type);
```

## Benefits

- **Catchable by domain**: `catch (PimcoreHttpCacheException $e)` catches all bundle exceptions
- **Self-documenting**: Factory method name explains the reason
- **Centralized messages**: Error messages defined in one place
- **Testable**: `$this->expectException(InvalidArgumentException::class)`

## Pattern

1. Create a marker interface extending `\Throwable` for your package
2. Create specific exception classes extending PHP's built-in exceptions
3. Have them implement the marker interface
4. Use `public static function because...(): self` factories
