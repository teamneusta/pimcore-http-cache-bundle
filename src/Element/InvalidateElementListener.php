<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InvalidateElementListener
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly DependentElementInvalidator $dependentElementInvalidator,
    ) {
    }

    public function onUpdate(ElementEventInterface $event): void
    {
        if ($this->shouldSkipInvalidation($event)) {
            return;
        }

        $this->invalidateWithDependentElements($event->getElement());
    }

    private function shouldSkipInvalidation(ElementEventInterface $event): bool
    {
        return $event->hasArgument('saveVersionOnly') || $event->hasArgument('autoSave');
    }

    public function onDelete(ElementEventInterface $event): void
    {
        $this->invalidateWithDependentElements($event->getElement());
    }

    private function invalidateWithDependentElements(ElementInterface $element): void
    {
        if (!$this->invalidateElement($element)) {
            return;
        }

        $this->dependentElementInvalidator->invalidate($element, fn ($e) => $this->invalidateElement($e));
    }

    private function invalidateElement(ElementInterface $element): bool
    {
        $invalidationEvent = $this->dispatcher->dispatch(ElementInvalidationEvent::fromElement($element));
        \assert($invalidationEvent instanceof ElementInvalidationEvent);

        if ($invalidationEvent->cancel) {
            return false;
        }

        $this->cacheInvalidator->invalidate($invalidationEvent->cacheTags());

        return true;
    }
}
