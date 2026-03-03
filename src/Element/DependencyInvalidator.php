<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\Element\ElementInterface;

final class DependencyInvalidator
{
    public function __construct(
        private readonly ElementRepository $elementRepository,
        private readonly ElementsConfig $config,
    ) {
    }

    /**
     * Invalidates dependent elements one level deep.
     * Dependencies of dependent elements are intentionally not traversed to prevent cycles.
     *
     * @param callable(ElementInterface): mixed $invalidate
     */
    public function invalidate(ElementInterface $source, callable $invalidate): void
    {
        $type = ElementType::tryFromElement($source);
        if ($type === null || !$this->config->isDependencyTraversalEnabled($type)) {
            return;
        }

        foreach ($source->getDependencies()->getRequiredBy() as $required) {
            if (!isset($required['id'], $required['type'])) {
                continue;
            }

            $dependentType = ElementType::tryFrom($required['type']);
            if ($dependentType === null || !$this->config->isDependentTypeEnabled($type, $dependentType)) {
                continue;
            }

            $element = match ($dependentType) {
                ElementType::Object => $this->elementRepository->findObject((int) $required['id']),
                ElementType::Document => $this->elementRepository->findDocument((int) $required['id']),
                ElementType::Asset => $this->elementRepository->findAsset((int) $required['id']),
            };

            if ($element) {
                $invalidate($element);
            }
        }
    }
}
