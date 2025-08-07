<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InvalidateElementListener
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function onUpdate(ElementEventInterface $event): void
    {
        if ($event->hasArgument('saveVersionOnly') || $event->hasArgument('autoSave')) {
            return;
        }

        $element = $event->getElement();

        $this->invalidateElement($element);
        $this->invalidateDependencies($element);
    }

    public function onDelete(ElementEventInterface $event): void
    {
        $element = $event->getElement();

        $this->invalidateElement($element);
        $this->invalidateDependencies($element);
    }

    private function invalidateElement(ElementInterface $element): void
    {
        $invalidationEvent = $this->dispatcher->dispatch(ElementInvalidationEvent::fromElement($element));
        \assert($invalidationEvent instanceof ElementInvalidationEvent);

        if ($invalidationEvent->cancel) {
            return;
        }

        $this->cacheInvalidator->invalidate($invalidationEvent->cacheTags());
    }

    private function invalidateDependencies(ElementInterface $element): void
    {
        if (!$element instanceof Concrete) {
            return;
        }

        foreach ($element->getDependencies()->getRequiredBy() as $dependency) {
            if ($dependency instanceof ElementInterface) {
                $this->invalidateElement($dependency);
            }
        }
    }
}
