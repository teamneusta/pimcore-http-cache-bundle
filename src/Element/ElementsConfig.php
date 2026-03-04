<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;

final readonly class ElementsConfig
{
    private function __construct(
        private bool $assetsEnabled,
        /** @var array<string, bool> */
        private array $assetTypes,
        private bool $assetDependentElementsEnabled,
        private DependentTypeConfig $assetDependentAssetConfig,
        private DependentTypeConfig $assetDependentDocumentConfig,
        private DependentTypeConfig $assetDependentObjectConfig,
        private bool $documentsEnabled,
        /** @var array<string, bool> */
        private array $documentTypes,
        private bool $documentDependentElementsEnabled,
        private DependentTypeConfig $documentDependentAssetConfig,
        private DependentTypeConfig $documentDependentDocumentConfig,
        private DependentTypeConfig $documentDependentObjectConfig,
        private bool $objectsEnabled,
        /** @var array<string, bool> */
        private array $objectTypes,
        /** @var array<string, bool> */
        private array $objectClasses,
        private bool $objectDependentElementsEnabled,
        private DependentTypeConfig $objectDependentAssetConfig,
        private DependentTypeConfig $objectDependentDocumentConfig,
        private DependentTypeConfig $objectDependentObjectConfig,
    ) {
    }

    /** @param array<mixed> $config */
    public static function fromArray(array $config): self
    {
        return new self(
            assetsEnabled: $config['assets']['enabled'] ?? false,
            assetTypes: $config['assets']['types'] ?? [],
            assetDependentElementsEnabled: $config['assets']['invalidate_dependent_elements']['enabled'] ?? false,
            assetDependentAssetConfig: DependentTypeConfig::fromArray($config['assets']['invalidate_dependent_elements']['types']['assets'] ?? []),
            assetDependentDocumentConfig: DependentTypeConfig::fromArray($config['assets']['invalidate_dependent_elements']['types']['documents'] ?? []),
            assetDependentObjectConfig: DependentTypeConfig::fromArray($config['assets']['invalidate_dependent_elements']['types']['objects'] ?? []),
            documentsEnabled: $config['documents']['enabled'] ?? false,
            documentTypes: $config['documents']['types'] ?? [],
            documentDependentElementsEnabled: $config['documents']['invalidate_dependent_elements']['enabled'] ?? false,
            documentDependentAssetConfig: DependentTypeConfig::fromArray($config['documents']['invalidate_dependent_elements']['types']['assets'] ?? []),
            documentDependentDocumentConfig: DependentTypeConfig::fromArray($config['documents']['invalidate_dependent_elements']['types']['documents'] ?? []),
            documentDependentObjectConfig: DependentTypeConfig::fromArray($config['documents']['invalidate_dependent_elements']['types']['objects'] ?? []),
            objectsEnabled: $config['objects']['enabled'] ?? false,
            objectTypes: $config['objects']['types'] ?? [],
            objectClasses: $config['objects']['classes'] ?? [],
            objectDependentElementsEnabled: $config['objects']['invalidate_dependent_elements']['enabled'] ?? false,
            objectDependentAssetConfig: DependentTypeConfig::fromArray($config['objects']['invalidate_dependent_elements']['types']['assets'] ?? []),
            objectDependentDocumentConfig: DependentTypeConfig::fromArray($config['objects']['invalidate_dependent_elements']['types']['documents'] ?? []),
            objectDependentObjectConfig: DependentTypeConfig::fromArray($config['objects']['invalidate_dependent_elements']['types']['objects'] ?? []),
        );
    }

    public function isEnabled(ElementType $type): bool
    {
        return match ($type) {
            ElementType::Asset => $this->assetsEnabled,
            ElementType::Document => $this->documentsEnabled,
            ElementType::Object => $this->objectsEnabled,
        };
    }

    public function isTypeEnabled(ElementType $elementType, string $type): bool
    {
        $types = match ($elementType) {
            ElementType::Asset => $this->assetTypes,
            ElementType::Document => $this->documentTypes,
            ElementType::Object => $this->objectTypes,
        };

        return $types[$type] ?? true;
    }

    public function isObjectClassEnabled(?string $class): bool
    {
        return null === $class || ($this->objectClasses[$class] ?? true);
    }

    public function isDependentElementsEnabled(ElementType $type): bool
    {
        return match ($type) {
            ElementType::Asset => $this->assetDependentElementsEnabled,
            ElementType::Document => $this->documentDependentElementsEnabled,
            ElementType::Object => $this->objectDependentElementsEnabled,
        };
    }

    public function isDependentTypeEnabled(ElementType $sourceType, ElementType $dependentType): bool
    {
        return $this->getDependentTypeConfig($sourceType, $dependentType)->isEnabled();
    }

    public function isDependentElementEnabled(ElementType $sourceType, ElementInterface $element): bool
    {
        $dependentType = ElementType::tryFromElement($element);

        if (null === $dependentType) {
            return false;
        }

        $dependentConfig = $this->getDependentTypeConfig($sourceType, $dependentType);

        if (!$dependentConfig->isEnabled()) {
            return false;
        }

        if (!$this->isTypeEnabled($dependentType, $element->getType())) {
            return false;
        }

        if (!$dependentConfig->isTypeEnabled($element->getType())) {
            return false;
        }

        if (ElementType::Object === $dependentType && $element instanceof Concrete) {
            return $this->isObjectClassEnabled($element->getClassName())
                && $dependentConfig->isObjectClassEnabled($element->getClassName());
        }

        return true;
    }

    private function getDependentTypeConfig(ElementType $source, ElementType $dependent): DependentTypeConfig
    {
        return match ($source) {
            ElementType::Asset => match ($dependent) {
                ElementType::Asset => $this->assetDependentAssetConfig,
                ElementType::Document => $this->assetDependentDocumentConfig,
                ElementType::Object => $this->assetDependentObjectConfig,
            },
            ElementType::Document => match ($dependent) {
                ElementType::Asset => $this->documentDependentAssetConfig,
                ElementType::Document => $this->documentDependentDocumentConfig,
                ElementType::Object => $this->documentDependentObjectConfig,
            },
            ElementType::Object => match ($dependent) {
                ElementType::Asset => $this->objectDependentAssetConfig,
                ElementType::Document => $this->objectDependentDocumentConfig,
                ElementType::Object => $this->objectDependentObjectConfig,
            },
        };
    }
}
