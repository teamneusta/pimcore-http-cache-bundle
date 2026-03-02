<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Dependency;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InvalidateElementListener
{
    /** @param array<mixed> $config */
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ElementRepository $elementRepository,
        private readonly array $config,
    ) {
    }

    public function onUpdate(ElementEventInterface $event): void
    {
        if ($event->hasArgument('saveVersionOnly') || $event->hasArgument('autoSave')) {
            return;
        }

        $this->invalidateWithDependencies($event->getElement());
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

        $type = ElementType::tryFrom($element->getType());
        if ($type !== null && $this->isDependencyTraversalEnabled($type)) {
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

    private function isDependencyTraversalEnabled(ElementType $type): bool
    {
        return $this->config[$this->configKey($type)]['invalidate_dependencies']['enabled'] ?? false;
    }

    private function invalidateDependencies(Dependency $dependency, ElementType $sourceType): void
    {
        $typesConfig = $this->config[$this->configKey($sourceType)]['invalidate_dependencies']['types'] ?? [];

        foreach ($dependency->getRequiredBy() as $required) {
            if (!isset($required['id'], $required['type'])) {
                continue;
            }

            $dependentType = ElementType::tryFrom($required['type']);
            if ($dependentType === null || !($typesConfig[$this->configKey($dependentType)] ?? false)) {
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

    private function configKey(ElementType $type): string
    {
        return match ($type) {
            ElementType::Asset => 'assets',
            ElementType::Document => 'documents',
            ElementType::Object => 'objects',
        };
    }
}
