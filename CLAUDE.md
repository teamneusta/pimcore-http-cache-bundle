# CLAUDE.md

## Project Overview

**teamneusta/pimcore-http-cache-bundle** — A Symfony/Pimcore bundle that adds automatic HTTP cache invalidation via cache tags. It extends FOSHttpCacheBundle for Pimcore, automatically tagging responses when elements are loaded and invalidating caches when elements (documents, assets, data objects) are saved or deleted. Works with reverse proxies like Varnish and Fastly.

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
├── Element/                      # Pimcore element handling, events, listeners
├── Exception/                    # Custom exceptions
└── NeustaPimcoreHttpCacheBundle.php
tests/
├── Unit/                         # Fast isolated tests (Prophecy mocks)
├── Integration/                  # Tests with real Pimcore kernel
└── app/                          # Minimal Pimcore app for integration tests
config/services.php               # Symfony DI service definitions
```

## How the Bundle Works

**Tagging**: When a Pimcore element is loaded during a request, the response is automatically tagged (`a{id}` for assets, `d{id}` for documents, `o{id}` for objects). Tags end up in the `X-Cache-Tags` header.

**Invalidation**: When an element is saved or deleted, the corresponding cache tags are invalidated via the reverse proxy. Skipped for `saveVersionOnly` and `autoSave`.

**Events**: `ElementTaggingEvent` and `ElementInvalidationEvent` let users add extra tags or cancel operations.

**Public API** (autowired services): `CacheActivator`, `CacheInvalidator`, `ResponseTagger`
**Value objects**: `CacheTags`, `CacheTag`, `CacheTypeFactory`

See `.claude/rules/bundle-usage.md` for detailed usage guide with examples.

## Coding Conventions

- **PHP**: 8.1 / 8.2; every file starts with `<?php declare(strict_types=1);`
- **Style**: Symfony coding standards via PHP CS Fixer (`@Symfony`, `@Symfony:risky`)
- **Static analysis**: PHPStan level 8
- **Classes**: `final` by default, constructor property promotion with `readonly`
- **Interfaces**: clean names, no `Interface` suffix
- **Namespace**: `Neusta\Pimcore\HttpCacheBundle\` (PSR-4 → `src/`)
- **Tests namespace**: `Neusta\Pimcore\HttpCacheBundle\Tests\` (PSR-4 → `tests/`)

See `.claude/rules/code-style.md` for full conventions with examples.

## Architecture & Patterns

- **Decorator pattern**: Services are heavily decorated (e.g., `ResponseTagger` chain: base → `RemoveDisabledTagsResponseTagger` → `OnlyWhenActiveResponseTagger` → `CacheTagCollectionResponseTagger`)
- **Immutable value objects**: `CacheTags` and `CacheTag` — use `with()` / static factories, never mutate
- **Event-driven**: Listens to Pimcore lifecycle events; dispatches custom events for extensibility
- **Single-method interfaces**: `CacheInvalidator::invalidate(CacheTags)` and `ResponseTagger::tag(CacheTags)`
- **Named exception factories**: `InvalidArgumentException::becauseCacheTagIsEmpty()`

See `.claude/rules/architecture.md` for details.

## Testing

- **PHPUnit 9.6** with Prophecy for mocking and `dg/bypass-finals` for final classes
- `@test` annotation with **snake_case** method names describing behavior
- Integration tests use `teamneusta/pimcore-testing-framework` with MariaDB
- CI matrix: PHP 8.1 (lowest + highest deps), PHP 8.2 (highest deps)

See `.claude/rules/testing.md` for full conventions with examples.

## Key Dependencies

- `pimcore/pimcore: ^10.6 || ^11.2`
- `friendsofsymfony/http-cache-bundle: ^2.17`
- `symfony/*: ^5.4 || ^6.4`

## CI / QA

GitHub Actions runs two workflows:
- **tests.yaml**: PHPUnit across PHP version matrix with MariaDB
- **qa.yaml**: composer validate, PHP CS Fixer check, PHPStan

See `.claude/rules/static-analysis.md` for PHPStan rules.
