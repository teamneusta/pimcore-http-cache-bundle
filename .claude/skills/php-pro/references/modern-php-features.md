# Modern PHP Features (8.1–8.3+)

## Readonly Properties & Classes

```php
<?php declare(strict_types=1);

// Readonly promoted properties (8.1)
final class Money
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {
    }
}

// Readonly class (8.2) — all properties implicitly readonly
final readonly class Coordinates
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }
}
```

## Enums (8.1)

```php
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Active => $target === self::Inactive || $target === self::Suspended,
            self::Inactive => $target === self::Active,
            self::Suspended => $target === self::Active,
        };
    }
}
```

## Intersection & Union Types

```php
// Union types (8.0)
function parse(string|int $input): Result|Error { }

// Intersection types (8.1)
function process(Countable&Iterator $collection): void { }

// DNF types (8.2) — combining union + intersection
function handle((Countable&Iterator)|null $collection): void { }

// true, false, null as standalone types (8.2)
function alwaysTrue(): true { return true; }
```

## Match Expression (8.0)

```php
$result = match ($statusCode) {
    200 => 'OK',
    301, 302 => 'Redirect',
    404 => 'Not Found',
    500 => 'Server Error',
    default => 'Unknown',
};
```

## Fibers (8.1)

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend('paused');
    echo "Resumed with: $value";
});

$result = $fiber->start();    // 'paused'
$fiber->resume('continue');   // Resumed with: continue
```

## Attributes (8.0)

```php
#[Route('/api/users', methods: ['GET'])]
#[Cache(maxAge: 3600)]
public function list(): JsonResponse { }

// Custom attribute
#[Attribute(Attribute::TARGET_METHOD)]
final class RateLimit
{
    public function __construct(
        public readonly int $maxRequests,
        public readonly int $windowSeconds,
    ) {
    }
}
```

## First-class Callables (8.1)

```php
$fn = strlen(...);
$mapped = array_map($entity->getId(...), $entities);
$filtered = array_filter($items, $this->isValid(...));
```

## Named Arguments (8.0)

```php
new DateTimeImmutable(datetime: '2024-01-01', timezone: new DateTimeZone('UTC'));
array_slice(array: $items, offset: 0, length: 10, preserve_keys: true);
```

## Typed Class Constants (8.3)

```php
final class Config
{
    public const string VERSION = '1.0.0';
    public const int MAX_RETRIES = 3;
    public const array DEFAULT_OPTIONS = ['timeout' => 30];
}
```

## json_validate() (8.3)

```php
if (json_validate($input)) {
    $data = json_decode($input, true, flags: JSON_THROW_ON_ERROR);
}
```

## #[\Override] Attribute (8.3)

```php
class Child extends Parent
{
    #[\Override]
    public function process(): void
    {
        // Compile error if parent method doesn't exist
    }
}
```
