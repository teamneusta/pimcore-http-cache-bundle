## Cancel caching behavior

You may encounter situations where you need to bypass the default caching behavior under certain conditions.
To achieve this, you can listen to specific events and cancel the normal caching process by setting the cancel property to true.

### Example for canceling the tagging behavior

```php

#[AsEventListener]
final class CancelTaggingListener
{
    public function __invoke(ElementTaggingEvent $event): void
    {
        if (ElementType::Object !== $event->elementType) {
            return;
        }
        
        if (123 === $event->element->getId()) {
            $event->cancel = true;
        }
    }
}
```

### Example for canceling the invalidation behavior

```php

#[AsEventListener]
final class CancelInvalidationListener
{
    public function __invoke(ElementInvalidationEvent $event): void
    {
        if (ElementType::Object !== $event->elementType) {
            return;
        }
        
        if (123 === $event->element->getId()) {
            $event->cancel = true;
        }
    }
}
```
