# Code Style

## General

- Every PHP file starts with `<?php declare(strict_types=1);` (no blank line between)
- Follow Symfony coding standards (`@Symfony` + `@Symfony:risky`)
- Indentation: 4 spaces for PHP, 2 spaces for JSON/YAML, tabs for .neon files
- Line endings: LF only
- Single quotes for strings; double quotes only when interpolation/escape sequences are needed
- Always run `composer cs:fix` before committing

## Classes

- Classes are `final` by default
- Interfaces have clean names — no `Interface` suffix (e.g., `CacheType`, not `CacheTypeInterface`)
- One blank line between methods, no blank lines between properties

## Constructors & Properties

- Always use **constructor property promotion** with `readonly`:
  ```php
  public function __construct(
      private readonly ResponseTagger $inner,
      private readonly CacheActivator $cacheActivator,
  ) {
  }
  ```
- Private constructor + public static factory methods for value objects:
  ```php
  private function __construct(
      public readonly string $tag,
      public readonly CacheType $type,
  ) {
  }

  public static function fromString(string $tag, ?CacheType $type = null): self
  ```

## Type Hints & Return Types

- Return types always present, including `void`
- Nullable with `?` prefix: `?Asset`
- Union types where needed: `CacheTag|self`
- PHPDoc only when PHP type system is insufficient (generics, array shapes):
  ```php
  /** @param array<string, bool> $types */
  /** @return array<array{tag: string, type: string}> */
  ```
- Omit doc blocks when native type hints are sufficient

## Trailing Commas

- Always use trailing commas in multiline structures — constructor params, arrays, function calls, match arms

## Global Functions

- Fully qualified for native functions: `\count()`, `\sprintf()`, `\assert()`, `\in_array()`

## Null Handling

- Null coalescing `??` preferred: `$type ?? CacheTypeFactory::createEmpty()`
- Null coalescing assignment `??=` for lazy init: `$prefixes ??= array_map(...)`
- Early return with assignment for null checks:
  ```php
  if (!$id = $element->getId()) {
      throw InvalidArgumentException::becauseElementHasNoId();
  }
  ```

## Exceptions

- Named static factory methods on exception classes:
  ```php
  throw InvalidArgumentException::becauseCacheTagIsEmpty();
  throw InvalidArgumentException::becauseCacheTypeIsReserved($type);
  ```
- Multi-line `\sprintf()` for longer messages is fine
- Custom exception interface (`PimcoreHttpCacheException`) as marker

## Modern PHP Features

- **Match expressions** over switch:
  ```php
  return match ($tag->type->type) {
      ElementType::Asset => $this->asset->isEnabled($tag),
      ElementType::Document => $this->document->isEnabled($tag),
      ElementType::Object => $this->object->isEnabled($tag),
  };
  ```
- **Arrow functions** for short closures: `fn ($tag) => CacheTag::fromString($tag)`
- **First-class callable syntax**: `array_map(CacheTag::fromElement(...), $elements)`
- **Variadic parameters** with union types: `CacheTag|self ...$tags`
- **Backed enums** with methods:
  ```php
  enum ElementType: string
  {
      case Asset = 'asset';
      case Document = 'document';
      case Object = 'object';
  }
  ```
- **Spread operator** for arrays: `[...$newTags, ...$tag->tags]`
- **`@no-named-arguments`** annotation on variadic constructors

## Concatenation

- Spaces around `.` operator: `'foo' . 'bar'` not `'foo'.'bar'`
