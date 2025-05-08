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
final class TagElementListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ElementTaggingEvent::class => 'onTagging',
        ];
    }

    public function onTagging(ElementTaggingEvent $event): void
    {
        if ($event->elementType !== ElementType::Object) {
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

final class InvalidateElementListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ElementInvalidationEvent::class => 'onInvalidation',
        ];
    }

    public function onInvalidation(ElementInvalidationEventt $event): void
    {
        if ($event->elementType !== ElementType::Object) {
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


