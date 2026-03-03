<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

readonly class ElementsConfig
{
    /**
     * @param array<string, bool> $assetTypes
     * @param array<string, bool> $assetDependentElementTypes
     * @param array<string, bool> $documentTypes
     * @param array<string, bool> $documentDependentElementTypes
     * @param array<string, bool> $objectTypes
     * @param array<string, bool> $objectClasses
     * @param array<string, bool> $objectDependentElementTypes
     */
    public function __construct(
        private bool $assetsEnabled,
        private array $assetTypes,
        private bool $assetDependentElementsEnabled,
        private array $assetDependentElementTypes,
        private bool $documentsEnabled,
        private array $documentTypes,
        private bool $documentDependentElementsEnabled,
        private array $documentDependentElementTypes,
        private bool $objectsEnabled,
        private array $objectTypes,
        private array $objectClasses,
        private bool $objectDependentElementsEnabled,
        private array $objectDependentElementTypes,
    ) {
    }

    /** @param array<mixed> $config */
    public static function fromArray(array $config): self
    {
        return new self(
            assetsEnabled: $config['assets']['enabled'] ?? false,
            assetTypes: $config['assets']['types'] ?? [],
            assetDependentElementsEnabled: $config['assets']['invalidate_dependent_elements']['enabled'] ?? false,
            assetDependentElementTypes: $config['assets']['invalidate_dependent_elements']['types'] ?? [],
            documentsEnabled: $config['documents']['enabled'] ?? false,
            documentTypes: $config['documents']['types'] ?? [],
            documentDependentElementsEnabled: $config['documents']['invalidate_dependent_elements']['enabled'] ?? false,
            documentDependentElementTypes: $config['documents']['invalidate_dependent_elements']['types'] ?? [],
            objectsEnabled: $config['objects']['enabled'] ?? false,
            objectTypes: $config['objects']['types'] ?? [],
            objectClasses: $config['objects']['classes'] ?? [],
            objectDependentElementsEnabled: $config['objects']['invalidate_dependent_elements']['enabled'] ?? false,
            objectDependentElementTypes: $config['objects']['invalidate_dependent_elements']['types'] ?? [],
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

    public function isDependentAssetInvalidationEnabled(ElementType $source): bool
    {
        return $this->dependentElementTypes($source)[ElementType::Asset->configKey()] ?? false;
    }

    public function isDependentDocumentInvalidationEnabled(ElementType $source): bool
    {
        return $this->dependentElementTypes($source)[ElementType::Document->configKey()] ?? false;
    }

    public function isDependentObjectInvalidationEnabled(ElementType $source): bool
    {
        return $this->dependentElementTypes($source)[ElementType::Object->configKey()] ?? false;
    }

    /** @return array<string, bool> */
    private function dependentElementTypes(ElementType $source): array
    {
        return match ($source) {
            ElementType::Asset => $this->assetDependentElementTypes,
            ElementType::Document => $this->documentDependentElementTypes,
            ElementType::Object => $this->objectDependentElementTypes,
        };
    }
}
