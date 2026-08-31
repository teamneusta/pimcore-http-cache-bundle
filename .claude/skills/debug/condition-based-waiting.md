# Condition-Based Waiting

Replace arbitrary timeouts with condition polling.

## Problem

```php
// Bad: arbitrary sleep
sleep(5); // "Should be enough time"
$result = $cache->get('key');
```

## Solution

```php
// Good: poll for condition
$maxAttempts = 50;
$interval = 100_000; // 100ms in microseconds

for ($i = 0; $i < $maxAttempts; $i++) {
    $result = $cache->get('key');
    if (null !== $result) {
        break;
    }
    usleep($interval);
}
```

## When to Use

- Waiting for async operations (cache propagation, queue processing)
- Integration tests that depend on external state
- CI/CD pipelines waiting for services

## Rules

- Always have a maximum wait time
- Poll at reasonable intervals (don't busy-wait)
- Fail with clear message when timeout expires
- Log what you're waiting for (aids debugging)
