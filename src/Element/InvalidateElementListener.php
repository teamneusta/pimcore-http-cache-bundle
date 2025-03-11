<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidatorInterface;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InvalidateElementListener
{
    public function __construct(
        private readonly CacheInvalidatorInterface $cacheInvalidator,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function onUpdated(ElementEventInterface $event): void
    {
        if ($event->hasArgument('saveVersionOnly') || $event->hasArgument('autoSave')) {
            return;
        }

        $this->invalidateElement($event->getElement());
    }

    public function onDeleted(ElementEventInterface $event): void
    {
        $this->invalidateElement($event->getElement());
    }

    private function invalidateElement(ElementInterface $element): void
    {
        $invalidationEvent = $this->dispatcher->dispatch(ElementInvalidationEvent::fromElement($element));
        \assert($invalidationEvent instanceof ElementInvalidationEvent);

        if ($invalidationEvent->cancel) {
            return;
        }

        $this->cacheInvalidator->invalidateElement($invalidationEvent->element, $invalidationEvent->elementType);
        $this->cacheInvalidator->invalidateElementTags($invalidationEvent->cacheTags, $invalidationEvent->elementType);
    }
}
