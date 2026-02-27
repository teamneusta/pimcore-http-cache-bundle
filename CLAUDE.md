# CLAUDE.md

## Project Overview

**teamneusta/pimcore-http-cache-bundle** — A Symfony/Pimcore bundle that adds active HTTP cache invalidation via cache tags. It extends FOSHttpCacheBundle for Pimcore, automatically tagging responses and invalidating caches when Pimcore elements (documents, assets, data objects) change.

## Quick Reference

```bash
# Install dependencies (Docker)
bin/composer install

# Run tests
bin/composer tests            # or: bin/run-tests

# Code style
bin/composer cs:fix           # fix issues
bin/composer cs:check         # check only (dry-run)

# Static analysis
bin/composer phpstan          # PHPStan level 8
```

Without Docker, use `composer` directly instead of `bin/composer`.

## Project Structure

```
src/                              # Main source (Neusta\Pimcore\HttpCacheBundle\)
├── Adapter/FOSHttpCache/         # FOSHttpCache adapter implementations
├── Cache/
│   ├── CacheInvalidator/         # Invalidation decorators
│   ├── CacheTagChecker/          # Tag validation per element type
│   ├── CacheType/                # Cache type strategy implementations
│   └── ResponseTagger/           # Response tagging decorator chain
├── DependencyInjection/          # Symfony DI extension + compiler passes
├── Element/                      # Pimcore element handling
├── Exception/                    # Custom exceptions
└── NeustaPimcoreHttpCacheBundle.php
tests/
├── Unit/                         # Fast isolated tests (Prophecy mocks)
├── Integration/                  # Tests with real Pimcore kernel
└── app/                          # Minimal Pimcore app for integration tests
config/services.php               # Symfony DI service definitions
```

## Coding Conventions

- **PHP**: 8.1 / 8.2; every file starts with `declare(strict_types=1);`
- **Style**: Symfony coding standards via PHP CS Fixer (`@Symfony`, `@Symfony:risky`)
- **Static analysis**: PHPStan level 8
- **Indentation**: 4 spaces for PHP; 2 spaces for JSON/YAML; tabs for .neon
- **Line endings**: LF
- **Namespace**: `Neusta\Pimcore\HttpCacheBundle\` (PSR-4 → `src/`)
- **Tests namespace**: `Neusta\Pimcore\HttpCacheBundle\Tests\` (PSR-4 → `tests/`)

## Architecture & Patterns

- **Decorator pattern**: Services are heavily decorated (e.g., `ResponseTagger` chain: base → `RemoveDisabledTagsResponseTagger` → `OnlyWhenActiveResponseTagger` → `CacheTagCollectionResponseTagger`)
- **Immutable value objects**: `CacheTags` (collection) and `CacheTag` use `with()` methods / readonly properties
- **Event-driven**: Listens to Pimcore element lifecycle events; dispatches `ElementTaggingEvent` and `ElementInvalidationEvent`
- **Single-method interfaces**: `CacheInvalidator::invalidate(CacheTags)` and `ResponseTagger::tag(CacheTags)`
- **Cache tag format**: Assets `a{id}`, Documents `d{id}`, Objects `o{id}`

## Testing

- **PHPUnit 9.6** with Prophecy for mocking and `dg/bypass-finals` for final classes
- Integration tests use `teamneusta/pimcore-testing-framework` with a MariaDB service
- Test attributes: `#[ConfigureExtension]`, `#[ConfigureRoute]`
- CI matrix: PHP 8.1 (lowest + highest deps), PHP 8.2 (highest deps)

## Key Dependencies

- `pimcore/pimcore: ^10.6 || ^11.2`
- `friendsofsymfony/http-cache-bundle: ^2.17`
- `symfony/*: ^5.4 || ^6.4`

## CI / QA

GitHub Actions runs two workflows:
- **tests.yaml**: PHPUnit across PHP version matrix with MariaDB
- **qa.yaml**: composer validate, PHP CS Fixer check, PHPStan
