<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\Element\ElementInterface;

final class DependentElementFinder
{
    public function __construct(
        private readonly ElementRepository $elementRepository,
        private readonly ElementsConfig $config,
    ) {
    }

    /**
     * @return list<ElementInterface>
     */
    public function findFor(ElementInterface $source): array
    {
        $sourceType = ElementType::tryFromElement($source);

        if (null === $sourceType || !$this->config->isDependentElementsEnabled($sourceType)) {
            return [];
        }

        $elements = [];

        foreach ($source->getDependencies()->getRequiredBy() as $required) {
            if (!isset($required['id'], $required['type'])) {
                continue;
            }

            $dependentType = ElementType::tryFrom($required['type']);

            if (null === $dependentType) {
                continue;
            }

            $element = match ($dependentType) {
                ElementType::Asset => $this->elementRepository->findAsset((int) $required['id']),
                ElementType::Document => $this->elementRepository->findDocument((int) $required['id']),
                ElementType::Object => $this->elementRepository->findObject((int) $required['id']),
            };

            if (null !== $element && $this->config->isDependentElementEnabled($sourceType, $element)) {
                $elements[] = $element;
            }
        }

        return $elements;
    }
}
