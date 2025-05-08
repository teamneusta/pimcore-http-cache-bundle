## Additional Tags

You may encounter scenarios where you need to tag a response with additional tags or invalidate specific tags when an element's tag is invalidated.
This often occurs when elements are related to one another, or when you want to invalidate a specific tag in response to the invalidation of a particular element.
To handle such cases, you can listen to the relevant events and add or invalidate tags as needed.

#### Example for adding additional tags

```php

#[AsEventListener]
final class TagElementListener
{
    public function __invoke(ElementTaggingEvent $event): void
    {
        if (ElementType::Object !== $event->elementType) {
            return;
        }
        
        if ($event->element instanceof MyCustomObjectClass) {
            $event->addTag(CacheTag::fromString('my_custom_tag'));
        }
    }
}
```

##### Example for invalidating additional tags

```php

#[AsEventListener]
final class InvalidateElementListener
{
    public function __invoke(ElementInvalidationEvent $event): void
    {
        if (ElementType::Object !== $event->elementType) {
            return;
        }
        
        if (MyCustomObjectClass instanceof $event->element) {
            $event->cacheTags->add(CacheTag::fromString('my_custom_tag'));
        }
    }
}
```
