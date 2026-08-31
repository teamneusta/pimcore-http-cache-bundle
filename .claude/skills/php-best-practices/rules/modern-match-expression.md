# modern-match-expression — Use match over switch

**Priority**: CRITICAL

Use PHP 8.0+ `match` expression instead of `switch` for value mapping.

## Rule

Replace `switch` statements with `match` when mapping input values to output values.

## Bad

```php
<?php declare(strict_types=1);

switch ($tag->type->type) {
    case ElementType::Asset:
        $result = $this->asset->isEnabled($tag);
        break;
    case ElementType::Document:
        $result = $this->document->isEnabled($tag);
        break;
    case ElementType::Object:
        $result = $this->object->isEnabled($tag);
        break;
    default:
        throw new \LogicException('Unknown type');
}

return $result;
```

## Good

```php
<?php declare(strict_types=1);

return match ($tag->type->type) {
    ElementType::Asset => $this->asset->isEnabled($tag),
    ElementType::Document => $this->document->isEnabled($tag),
    ElementType::Object => $this->object->isEnabled($tag),
};
```

## Key Differences from switch

| `switch` | `match` |
|----------|---------|
| Loose comparison (`==`) | Strict comparison (`===`) |
| Statement (no return) | Expression (returns a value) |
| Falls through without `break` | No fallthrough |
| Unmatched = silent | Unmatched = `UnhandledMatchError` |

## When to Keep switch

Use `switch` when you need:
- Multiple statements per case (side effects)
- Fallthrough behavior between cases
- Complex logic that doesn't map input → output
