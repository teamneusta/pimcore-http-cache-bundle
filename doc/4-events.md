
## Events

You can listen to the following events:

**ElementTaggingEvent**: Triggered before a Pimcore element is tagged; allows adding tags or canceling the tagging process.

**ElementInvalidationEvent**: Triggered before a Pimcore element is invalidated; allows canceling the invalidation process or performing custom actions.

This allows you to add additional tags, cancel the tagging/invalidation process, or implement custom logic.

### ElementInvalidationEvent: distinguishing update from delete

`ElementInvalidationEvent` exposes a `type` property of type `InvalidationType`, which tells you whether the invalidation was triggered by a save or a delete:

```php
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidationType;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class MyInvalidationListener
{
    public function __invoke(ElementInvalidationEvent $event): void
    {
        if (InvalidationType::Delete === $event->type) {
            // React differently on delete vs update
        }
    }
}
```

| `InvalidationType` | Triggered by |
|---|---|
| `InvalidationType::Update` | Element saved (`POST_UPDATE`) |
| `InvalidationType::Delete` | Element deleted (`PRE_DELETE`) |
