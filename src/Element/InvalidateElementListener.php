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
        private readonly ElementsConfig $config,
    ) {
    }

    public function onUpdate(ElementEventInterface $event): void
    {
        if ($this->shouldSkipInvalidation($event)) {
            return;
        }

        $this->invalidateWithDependencies($event->getElement());
    }

    private function shouldSkipInvalidation(ElementEventInterface $event): bool
    {
        return $event->hasArgument('saveVersionOnly') || $event->hasArgument('autoSave');
    }

    public function onDelete(ElementEventInterface $event): void
    {
        $this->invalidateWithDependencies($event->getElement());
    }

    private function invalidateWithDependencies(ElementInterface $element): void
    {
        if (!$this->invalidateElement($element)) {
            return;
        }

        $type = ElementType::tryFromElement($element);
        if ($type !== null && $this->config->isDependencyTraversalEnabled($type)) {
            $this->invalidateDependencies($element->getDependencies(), $type);
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

    private function invalidateDependencies(Dependency $dependency, ElementType $sourceType): void
    {
        foreach ($dependency->getRequiredBy() as $required) {
            if (!isset($required['id'], $required['type'])) {
                continue;
            }

            $dependentType = ElementType::tryFrom($required['type']);
            if ($dependentType === null || !$this->config->isDependentTypeEnabled($sourceType, $dependentType)) {
                continue;
            }

            $element = match ($dependentType) {
                ElementType::Object => $this->elementRepository->findObject((int) $required['id']),
                ElementType::Document => $this->elementRepository->findDocument((int) $required['id']),
                ElementType::Asset => $this->elementRepository->findAsset((int) $required['id']),
            };

            if ($element) {
                $this->invalidateElement($element);
            }
        }
    }
}
