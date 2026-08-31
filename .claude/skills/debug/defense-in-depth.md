# Defense in Depth

Add validation at multiple layers after finding a root cause.

## Technique

After fixing the root cause, add guards at each layer the bad data passed through. This prevents similar issues and provides better error messages.

## Example

Root cause: Element without ID being tagged.

```
Layer 1 (source): Check element has ID before creating event
Layer 2 (event):  Validate in ElementTaggingEvent::fromElement()
Layer 3 (tag):    Validate in CacheTag::fromElement() — already exists
Layer 4 (string): Validate in CacheTag::fromString() — already exists
```

## Rules

- Fix the root cause FIRST
- Then add guards at boundaries
- Each guard should have a clear, specific error message
- Don't add guards everywhere — only at component boundaries
- Guards should be assertions or early returns, not try/catch
