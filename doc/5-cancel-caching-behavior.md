## Cancel caching behavior

You may encounter situations where you need to bypass the default caching behavior under certain conditions.
To achieve this, you can listen to specific events and cancel the normal caching process by setting the cancel property to true.

### Example for canceling the tagging behavior
```php
final class CancelTaggingListener implements EventSubscriberInterface
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
        
        if ($event->element->getId() === 123) {
            $event->cancel = true;
        }
    }
}
```

### Example for canceling the invalidation behavior
```php
final class CancelInvalidationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ElementInvalidationEvent::class => 'onInvalidation',
        ];
    }

    public function onInvalidation(ElementInvalidationEvent $event): void
    {
        if ($event->elementType !== ElementType::OBJECT) {
            return;
        }
        
        if ($event->element->getId() === 123) {
            $event->cancel = true;
        }
    }
}
```
