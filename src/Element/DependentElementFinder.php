<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;

final class DependentElementFinder
{
    public function __construct(
        private readonly ElementRepository $elementRepository,
        private readonly ElementsConfig $config,
    ) {
    }

    /**
     * Returns dependent elements one level deep.
     * Dependencies of dependent elements are intentionally not traversed to prevent cycles.
     *
     * @return list<ElementInterface>
     */
    public function findFor(ElementInterface $source): array
    {
        $sourceType = ElementType::tryFromElement($source);
        if ($sourceType === null || !$this->config->isDependentElementsEnabled($sourceType)) {
            return [];
        }

        $elements = [];

        foreach ($source->getDependencies()->getRequiredBy() as $required) {
            if (!isset($required['id'], $required['type'])) {
                continue;
            }

            $dependentType = ElementType::tryFrom($required['type']);
            if ($dependentType === null) {
                continue;
            }

            $depConfig = $this->config->getDependentTypeConfig($sourceType, $dependentType);
            if (!$depConfig->isEnabled()) {
                continue;
            }

            $element = match ($dependentType) {
                ElementType::Asset => $this->elementRepository->findAsset((int) $required['id']),
                ElementType::Document => $this->elementRepository->findDocument((int) $required['id']),
                ElementType::Object => $this->elementRepository->findObject((int) $required['id']),
            };

            if ($element && $this->isElementEnabled($dependentType, $depConfig, $element)) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    private function isElementEnabled(ElementType $type, DependentTypeConfig $depConfig, ElementInterface $element): bool
    {
        // Global config check
        if (!$this->config->isTypeEnabled($type, $element->getType())) {
            return false;
        }

        // Dependent-specific config check
        if (!$depConfig->isTypeEnabled($element->getType())) {
            return false;
        }

        if ($type === ElementType::Object && $element instanceof Concrete) {
            return $this->config->isClassEnabled($element->getClassName())
                && $depConfig->isClassEnabled($element->getClassName());
        }

        return true;
    }
}
