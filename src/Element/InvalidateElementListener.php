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
        private readonly DependentElementFinder $dependentElementFinder,
    ) {
    }

    public function onUpdate(ElementEventInterface $event): void
    {
        if (!$event->hasArgument('saveVersionOnly') || !$event->hasArgument('autoSave')) {
            $this->invalidateWithDependentElements($event->getElement());
        }
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

        foreach ($this->dependentElementFinder->findFor($element) as $dependent) {
            $this->invalidateElement($dependent);
        }
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
