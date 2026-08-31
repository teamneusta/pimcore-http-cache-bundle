# solid-dependency-inversion — Depend on abstractions

**Priority**: HIGH

High-level modules should not depend on low-level modules. Both should depend on abstractions.

## Rule

Depend on interfaces, not concrete implementations. Define interfaces in the domain layer; implement them in infrastructure.

## Bad

```php
<?php declare(strict_types=1);

// High-level module depends directly on low-level FOSHttpCache
final class TagElementListener
{
    public function __construct(
        private readonly FosResponseTagger $responseTagger, // Concrete dependency
    ) {
    }

    public function __invoke(ElementEventInterface $event): void
    {
        $this->responseTagger->addTags([$tag]); // Coupled to FOSHttpCache API
    }
}
```

## Good

```php
<?php declare(strict_types=1);

// Own interface — not coupled to any implementation
interface ResponseTagger
{
    public function tag(CacheTags $tags): void;
}

// High-level module depends on abstraction
final class TagElementListener
{
    public function __construct(
        private readonly ResponseTagger $responseTagger,
    ) {
    }

    public function __invoke(ElementEventInterface $event): void
    {
        $this->responseTagger->tag($tags);
    }
}

// Low-level adapter implements the abstraction
final class ResponseTaggerAdapter implements ResponseTagger
{
    public function __construct(
        private readonly FosResponseTagger $responseTagger,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        if ($tags->isEmpty()) {
            return;
        }
        $this->responseTagger->addTags($tags->toArray());
    }
}
```

## Benefits

- Swap implementations without changing business logic (e.g., switch from Varnish to Fastly)
- Test high-level code with simple mocks/stubs
- Decoration chains become possible (each decorator implements the same interface)
- Clear dependency direction: domain ← infrastructure

## In DI Configuration

```php
// Wire the interface to the adapter
$services->set('neusta_pimcore_http_cache.response_tagger', ResponseTaggerAdapter::class);
$services->alias(ResponseTagger::class, 'neusta_pimcore_http_cache.response_tagger');
```
