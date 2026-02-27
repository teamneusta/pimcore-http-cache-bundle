# Root Cause Tracing

Trace bugs backward through the call stack to find the original trigger.

## Technique

Start at the error. Work backward. At each step ask: "What called this with the bad value?"

```
Error: InvalidArgumentException at CacheTag.php:25
  ← Called by CacheTags::fromStrings() with empty string
    ← Called by TagElementListener with element tags
      ← Called by Pimcore POST_LOAD event
        ← Element loaded with no ID (ROOT CAUSE)
```

## Steps

1. **Start at the error** — note the exact value that's wrong
2. **Find the caller** — who passed the bad value?
3. **Check the caller's caller** — where did *they* get it?
4. **Repeat** until you find where the bad value originated
5. **Fix at the source** — not at the symptom

## Example

```
Symptom: "Cache tag must not be empty" exception

Step 1: CacheTag::fromString('') — empty string passed
Step 2: CacheTags::fromStrings($tags) — $tags contains ''
Step 3: TagElementListener gets tags from event
Step 4: ElementTaggingEvent::fromElement($element)
Step 5: Element->getId() returns null → (string) null = ''

Root cause: Element without ID being tagged
Fix: Check for null ID before creating tag (not: catch exception)
```

## Rules

- Never fix at the symptom level
- Trace ALL the way back to the source
- If you can't trace further, add logging and reproduce
- The root cause is often 3-5 levels above the error
