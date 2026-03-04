# type-strict-mode — Declare strict types

**Priority**: CRITICAL

Every PHP file must declare strict types on the first line after the opening tag.

## Rule

```php
<?php declare(strict_types=1);
```

## Why

- Prevents implicit type coercion (e.g., `"123"` silently becoming `123`)
- Catches type bugs at call time instead of producing unexpected results
- Makes code behavior predictable and explicit

## Bad

```php
<?php

namespace App\Service;

// Without strict_types, PHP silently coerces "42" to 42
function multiply(int $a, int $b): int
{
    return $a * $b;
}

multiply("3", "4"); // Returns 12 — no error!
```

## Good

```php
<?php declare(strict_types=1);

namespace App\Service;

function multiply(int $a, int $b): int
{
    return $a * $b;
}

multiply("3", "4"); // TypeError thrown!
```

## Notes

- Must be the very first statement in the file (after `<?php`)
- No blank line between `<?php` and `declare`
- Affects the file where it's declared, not called files
