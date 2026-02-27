# modern-enums — Use enums instead of constants

**Priority**: CRITICAL

Use PHP 8.1+ backed enums instead of class constants or magic strings.

## Rule

Replace sets of related constants with a backed enum. Add methods to enums for associated behavior.

## Bad

```php
<?php declare(strict_types=1);

final class ElementType
{
    public const ASSET = 'asset';
    public const DOCUMENT = 'document';
    public const OBJECT = 'object';
}

function process(string $type): void
{
    if ($type === ElementType::ASSET) { /* ... */ }
}

process('invalid'); // No error — any string accepted
```

## Good

```php
<?php declare(strict_types=1);

enum ElementType: string
{
    case Asset = 'asset';
    case Document = 'document';
    case Object = 'object';

    public static function fromElement(ElementInterface $element): self
    {
        return self::from(Service::getElementType($element) ?? '');
    }
}

function process(ElementType $type): void
{
    // Type-safe — only valid enum cases accepted
}

process(ElementType::Asset); // Valid
process('invalid');          // TypeError
```

## Enums with Methods

Enums can have methods, implement interfaces, and use traits:

```php
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}
```

## Use match with Enums

Combine enums with `match` for exhaustive handling:

```php
return match ($tag->type->type) {
    ElementType::Asset => $this->asset->isEnabled($tag),
    ElementType::Document => $this->document->isEnabled($tag),
    ElementType::Object => $this->object->isEnabled($tag),
};
```

PHP warns if a case is missing — no forgotten branches.

## When to Use

- Replacing a set of related string/int constants
- Modeling a finite set of states or types
- When you need type safety for a limited set of values
