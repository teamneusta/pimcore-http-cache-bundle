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
        $type = ElementType::tryFromElement($source);
        if ($type === null || !$this->config->isDependentElementsEnabled($type)) {
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

            $enabled = match ($dependentType) {
                ElementType::Asset => $this->config->isDependentAssetInvalidationEnabled($type),
                ElementType::Document => $this->config->isDependentDocumentInvalidationEnabled($type),
                ElementType::Object => $this->config->isDependentObjectInvalidationEnabled($type),
            };

            if (!$enabled) {
                continue;
            }

            $element = match ($dependentType) {
                ElementType::Asset => $this->elementRepository->findAsset((int) $required['id']),
                ElementType::Document => $this->elementRepository->findDocument((int) $required['id']),
                ElementType::Object => $this->elementRepository->findObject((int) $required['id']),
            };

            if ($element && $this->isElementEnabled($dependentType, $element)) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    private function isElementEnabled(ElementType $type, ElementInterface $element): bool
    {
        if (!$this->config->isTypeEnabled($type, $element->getType())) {
            return false;
        }

        if ($type === ElementType::Object && $element instanceof Concrete) {
            return $this->config->isClassEnabled($element->getClassName());
        }

        return true;
    }
}
