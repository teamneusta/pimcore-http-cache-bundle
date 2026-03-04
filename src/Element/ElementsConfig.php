<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

final readonly class ElementsConfig
{
    public function __construct(
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

    public function isClassEnabled(string $class): bool
    {
        return $this->objectClasses[$class] ?? true;
    }

    public function isDependentElementsEnabled(ElementType $type): bool
    {
        return match ($type) {
            ElementType::Asset => $this->assetDependentElementsEnabled,
            ElementType::Document => $this->documentDependentElementsEnabled,
            ElementType::Object => $this->objectDependentElementsEnabled,
        };
    }

    public function getDependentTypeConfig(ElementType $source, ElementType $dependent): DependentTypeConfig
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
