## Additional Tags

You may encounter scenarios where you need to tag a response with additional tags or invalidate specific tags when an element's tag is invalidated.
This often occurs when elements are related to one another, or when you want to invalidate a specific tag in response to the invalidation of a particular element.
To handle such cases, you can listen to the relevant events and add or invalidate tags as needed.

#### Example for adding additional tags
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
            $event->addTag(CacheTag::fromString('my_custom_tag'));
        }
    }
}
```

##### Example for invalidating additional tags
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
            $event->cacheTags->add(CacheTag::fromString('my_custom_tag'));
        }
    }
}
```
