## Custom Cache Types

You can define custom cache types in the configuration file, allowing you to create and manage your own cache types, enabling or disabling them as needed.

#### Example configuration to define custom cache types and enable or disable them

```yaml
neusta_pimcore_http_cache:
    cache_types:
        my_custom_cache_type: true
        my_other_custom_cache_type: false
```

#### Example for tagging a custom cache type

```php

#[AsEventListener]
final class TagElementListener
{
    public function __invoke(ElementTaggingEvent $event): void
    {
        if (ElementType::Object !== $event->elementType) {
            return;
        }
        
        if ($event->element instanceof MyCustomObjectClass) {{
            $event->cacheTags->add(
                CacheTag::fromString('my_custom_tag'),
                CacheTypeFactory::createFromString('my_custom_cache_type')
            );
        }
    }
}
```

#### Example for invalidating a custom cache type

```php

#[AsEventListener]
final class InvalidateElementListener
{
    public function __invoke(ElementInvalidationEventt $event): void
    {
        if (ElementType::Object !== $event->elementType) {
            return;
        }
        
        if ($event->element instanceof MyCustomObjectClass) {{
            $event->addTag(
                CacheTag::fromString('my_custom_tag'),
                CacheTypeFactory::createFromString('my_custom_cache_type')
            );
        }
    }
}
```
