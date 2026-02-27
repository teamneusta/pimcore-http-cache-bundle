# Review Checklist — Full Details

## 1. Dead Code Detection

### What to Check

- **Unused imports** — `use` statements that aren't referenced in the file
- **Unused methods** — private/protected methods not called within the class
- **Unused variables** — assigned but never read
- **Unused classes** — new files that nothing references (check `services.php`, other classes)
- **Unreachable code** — code after return/throw, impossible conditions
- **Commented-out code** — delete it, git remembers
- **Orphaned services** — services defined in `config/services.php` that are no longer needed
- **Dead config options** — configuration tree entries that aren't read anywhere

### How to Check

```bash
# Search for usages of a class/method
grep -r "ClassName" src/ tests/ config/

# Check if a service ID is referenced
grep -r "service_id" config/ src/DependencyInjection/
```

### Severity: WARNING

Dead code is not a bug, but it adds confusion and maintenance burden. Flag it for removal.

---

## 2. Architecture Compliance

### What to Check

**Classes:**
- [ ] New classes are `final` (unless there's a documented reason)
- [ ] Interfaces have clean names — no `Interface` suffix
- [ ] Value objects are immutable (readonly properties, `with()` methods)
- [ ] Single responsibility — class does one thing

**Constructors:**
- [ ] Constructor property promotion used
- [ ] Properties are `readonly` where possible
- [ ] Trailing comma on last parameter
- [ ] Dependencies injected, not created internally

**Patterns:**
- [ ] Decorator pattern used for cross-cutting concerns (not inheritance)
- [ ] Single-method interfaces for core contracts
- [ ] Events for extensibility (not direct coupling)
- [ ] Adapter pattern for external dependencies (FOSHttpCache)
- [ ] Named static factories for exceptions (`becauseReasonHere()`)

**Naming:**
- [ ] Cache tag format respected: `a{id}`, `d{id}`, `o{id}`
- [ ] Namespace follows PSR-4: `Neusta\Pimcore\HttpCacheBundle\`
- [ ] Test namespace: `Neusta\Pimcore\HttpCacheBundle\Tests\`

### Red Flags

- Non-final class without `@internal` annotation
- Mutable properties on value objects
- Service locator or `new` in a service constructor
- God class with many responsibilities
- Breaking the decorator chain without reason

### Severity: BLOCKER for pattern violations, WARNING for naming

---

## 3. Documentation Sync

### What to Check

- [ ] **CHANGELOG.md** — Does the change warrant a changelog entry?
  - New features: YES
  - Bug fixes: YES
  - Internal refactoring: NO (unless it affects public API)
- [ ] **doc/ files** — If behavior changed, are docs updated?
  - New config options → `doc/2-configuration.md`
  - New events → `doc/4-events.md`
  - New cache types → `doc/7-custom-cache-types.md`
- [ ] **PHPDoc** — Complex types annotated? (`@param array<string, bool>`, `@return list<CacheTag>`)
  - Required when PHP type system is insufficient (generics, array shapes)
  - NOT required for simple scalar types
- [ ] **README.md** — Major features or install process changed?
- [ ] **CLAUDE.md / rules** — Architecture or conventions changed?

### When to Skip

- PHPDoc for obvious types (`string`, `int`, `void`)
- CHANGELOG for pure test changes
- README for internal changes

### Severity: WARNING

---

## 4. Test Coverage

### What to Check

- [ ] Every new public method has at least one test
- [ ] Every bug fix has a regression test
- [ ] Edge cases covered (empty input, null, boundary values)
- [ ] Tests follow project conventions:
  - `@test` annotation (not `test` prefix)
  - snake_case method names describing behavior
  - Prophecy for mocking with typed `ObjectProphecy` properties
  - `self::assert*()` for state verification
  - Unit tests mirror `src/` structure in `tests/Unit/`
- [ ] Integration tests use correct base class (`ConfigurableKernelTestCase` or `ConfigurableWebTestcase`)
- [ ] No test-only code in production classes

### How to Check

```bash
# Run tests
composer tests

# Check what's tested
grep -r "function it_" tests/ | wc -l

# Find untested classes
diff <(find src -name "*.php" | sed 's/src/tests\/Unit/' | sed 's/.php/Test.php/' | sort) \
     <(find tests/Unit -name "*Test.php" | sort)
```

### Red Flags

- New class with no corresponding test file
- Test that doesn't assert anything
- Test that mocks the class it's testing
- Integration test without `ResetDatabase` trait

### Severity: BLOCKER for missing tests on new code, WARNING for missing edge cases

---

## 5. Security

### What to Check

- [ ] **Input validation** — All external input validated before use
  - User input, query parameters, request bodies
  - Configuration values from untrusted sources
- [ ] **Type safety** — No unchecked casts, `mixed` minimized
- [ ] **Exception info leaks** — Error messages don't expose internals to users
- [ ] **Injection** — No string interpolation in:
  - SQL queries (use prepared statements)
  - Shell commands (use proper escaping)
  - Log messages with user data (use context array)
- [ ] **Serialization** — No `unserialize()` on untrusted data
- [ ] **File operations** — Path traversal checked if user input in paths

### Severity: BLOCKER

---

## 6. Performance

### What to Check

- [ ] **N+1 queries** — Loading related entities in loops
  - e.g., checking each tag by loading element from DB individually
- [ ] **Unnecessary loops** — Could use `array_map`, `array_filter`, or collection methods
- [ ] **Missing early returns** — Check cheapest conditions first
  ```php
  // Good: cheap check first
  if ($tags->isEmpty()) { return; }
  // Then expensive operations
  ```
- [ ] **Redundant work** — Same computation repeated, missing caching
- [ ] **String building in loops** — Use `implode()` or `sprintf()`
- [ ] **Memory** — Large collections processed all at once vs. generators

### This Project Specifically

- Tag checking loads elements from DB — is it necessary? Could it be cached?
- Decorator chain: is each decorator doing minimal work?
- Event dispatching: expensive listeners should be lazy

### Severity: WARNING (BLOCKER if O(n^2) or worse)

---

## 7. Backward Compatibility (BC) Breaks

### What to Check

- [ ] **Public API changes** — Services users autowire:
  - `CacheActivator`
  - `CacheInvalidator`
  - `ResponseTagger`
  - `CacheTags`, `CacheTag`
  - Events: `ElementTaggingEvent`, `ElementInvalidationEvent`
- [ ] **Removed/renamed public methods** — Breaking for anyone calling them
- [ ] **Changed method signatures** — New required parameters
- [ ] **Configuration changes** — Renamed/removed config keys under `neusta_pimcore_http_cache`
- [ ] **Service ID changes** — Renamed service IDs in `config/services.php`
- [ ] **Cache tag format changes** — Would invalidate existing caches
- [ ] **Event class changes** — Changed properties, removed methods
- [ ] **Interface changes** — Added methods to existing interfaces

### How to Assess

- **Internal** classes (`@internal`) — BC breaks allowed
- **Public** interfaces/classes — BC breaks require major version bump
- **Config** changes — Deprecate first, remove in next major

### Severity: BLOCKER for public API, WARNING for internal

---

## 8. Static Analysis & Code Style

### What to Check

- [ ] **PHPStan level 8** — Would new code pass?
  - All types declared (no `mixed`)
  - Generics on collections (`array<string, CacheTag>`)
  - Strict comparisons (`===` not `==`)
  - No dead code branches
- [ ] **strict_types** — `<?php declare(strict_types=1);` on every file
- [ ] **CS Fixer** — Would new code pass?
  - Symfony coding standards
  - Spaces around concatenation
  - Trailing commas in multiline structures
  - Constructor promotion + readonly
- [ ] **Fully qualified native functions** — `\count()`, `\sprintf()`, `\in_array()`

### How to Verify

```bash
composer phpstan
composer cs:check
```

### Severity: WARNING (easily fixable, but should not be merged without fixing)
