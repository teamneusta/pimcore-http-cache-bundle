<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Dependency;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InvalidateElementListener
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ElementRepository $elementRepository,
    ) {
    }

    public function onUpdate(ElementEventInterface $event): void
    {
        if ($event->hasArgument('saveVersionOnly') || $event->hasArgument('autoSave')) {
            return;
        }

        $element = $event->getElement();

        $this->invalidateElement($element);

        $this->invalidateDependencies($element->getDependencies());
    }

    public function onDelete(ElementEventInterface $event): void
    {
        $element = $event->getElement();

        $this->invalidateElement($element);
        $this->invalidateDependencies($element->getDependencies());
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

    private function invalidateDependencies(Dependency $dependency): void
    {
        $requiredBy = $dependency->getRequiredBy();
        foreach ($requiredBy as $required) {
            if (!isset($required['id'], $required['type'])) {
                continue;
            }

            $element = match (ElementType::tryFrom($required['type'])) {
                ElementType::Object => $this->elementRepository->findObject($required['id']),
                ElementType::Document => $this->elementRepository->findDocument($required['id']),
                ElementType::Asset => $this->elementRepository->findAsset($required['id']),
                default => null,
            };

            if ($element) {
                $this->invalidateElement($element);
            }
        }
    }
}
