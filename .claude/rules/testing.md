# Testing

## Setup

- Test framework: PHPUnit 9.6 with Prophecy for mocking
- Unit tests go in `tests/Unit/`, integration tests in `tests/Integration/`
- Mirror the `src/` directory structure in tests (e.g., `src/Cache/Foo.php` -> `tests/Unit/Cache/FooTest.php`)
- Use `dg/bypass-finals` when mocking final classes
- Integration tests use the Pimcore test kernel in `tests/app/`
- Run tests with `composer tests`

## Test Class Conventions

- All test classes are `final class`
- Unit tests: `{ClassUnderTest}Test` (e.g., `CacheActivatorTest`)
- Integration tests: `{Feature}Test` (e.g., `InvalidateAssetTest`)

## Method Naming

- Always use the `@test` annotation — never the `test` prefix
- Method names are **snake_case** and describe the expected behavior:
  ```php
  /** @test */
  public function it_must_be_activated_by_default(): void

  /** @test */
  public function response_is_invalidated_when_asset_is_updated(): void
  ```

## Mocking with Prophecy

- Use `ProphecyTrait` on the test class
- Declare mocks as typed `ObjectProphecy` properties with `@var` PHPDoc:
  ```php
  /** @var ObjectProphecy<CacheInvalidator> */
  private $cacheInvalidator;
  ```
- Create mocks in `setUp()` with `$this->prophesize()`
- Pass mocks to constructors via `->reveal()`
- Verify interactions with `shouldHaveBeenCalledOnce()`, `shouldNotHaveBeenCalled()`
- Use `Argument::any()`, `Argument::type()`, `Argument::which()` for flexible matching

## Assertions

- Use `self::assertTrue()`, `self::assertSame()`, etc. (static calls) for state verification
- Use Prophecy expectations for interaction verification
- Use `\assert()` for runtime type narrowing (not testing — this is production code):
  ```php
  \assert($taggingEvent instanceof ElementTaggingEvent);
  ```

## Data Providers

- Name them `{something}Provider` with `iterable` return type
- Use `yield` with descriptive named keys:
  ```php
  public function elementProvider(): iterable
  {
      yield 'Asset' => ['event' => new AssetEvent($asset->reveal())];
  }
  ```
- Reference via `@dataProvider` annotation

## Integration Tests

- Extend `ConfigurableKernelTestCase` or `ConfigurableWebTestcase`
- Use traits: `ArrangeCacheTest`, `ProphecyTrait`, `ResetDatabase`
- Use PHP 8 attributes: `#[ConfigureExtension]`, `#[ConfigureRoute]`
- Set up test data with `self::arrange()`
