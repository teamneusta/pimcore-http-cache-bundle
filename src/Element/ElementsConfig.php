<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

readonly class ElementsConfig
{
    /**
     * @param array<string, bool> $assetTypes
     * @param array<string, bool> $assetDependencyTypes
     * @param array<string, bool> $documentTypes
     * @param array<string, bool> $documentDependencyTypes
     * @param array<string, bool> $objectTypes
     * @param array<string, bool> $objectClasses
     * @param array<string, bool> $objectDependencyTypes
     */
    public function __construct(
        private bool $assetsEnabled,
        private array $assetTypes,
        private bool $assetDependencyTraversalEnabled,
        private array $assetDependencyTypes,
        private bool $documentsEnabled,
        private array $documentTypes,
        private bool $documentDependencyTraversalEnabled,
        private array $documentDependencyTypes,
        private bool $objectsEnabled,
        private array $objectTypes,
        private array $objectClasses,
        private bool $objectDependencyTraversalEnabled,
        private array $objectDependencyTypes,
    ) {
    }

    /** @param array<mixed> $config */
    public static function fromArray(array $config): self
    {
        return new self(
            assetsEnabled: $config['assets']['enabled'] ?? false,
            assetTypes: $config['assets']['types'] ?? [],
            assetDependencyTraversalEnabled: $config['assets']['invalidate_dependencies']['enabled'] ?? false,
            assetDependencyTypes: $config['assets']['invalidate_dependencies']['types'] ?? [],
            documentsEnabled: $config['documents']['enabled'] ?? false,
            documentTypes: $config['documents']['types'] ?? [],
            documentDependencyTraversalEnabled: $config['documents']['invalidate_dependencies']['enabled'] ?? false,
            documentDependencyTypes: $config['documents']['invalidate_dependencies']['types'] ?? [],
            objectsEnabled: $config['objects']['enabled'] ?? false,
            objectTypes: $config['objects']['types'] ?? [],
            objectClasses: $config['objects']['classes'] ?? [],
            objectDependencyTraversalEnabled: $config['objects']['invalidate_dependencies']['enabled'] ?? false,
            objectDependencyTypes: $config['objects']['invalidate_dependencies']['types'] ?? [],
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

    public function isDependencyTraversalEnabled(ElementType $type): bool
    {
        return match ($type) {
            ElementType::Asset => $this->assetDependencyTraversalEnabled,
            ElementType::Document => $this->documentDependencyTraversalEnabled,
            ElementType::Object => $this->objectDependencyTraversalEnabled,
        };
    }

    public function isDependentTypeEnabled(ElementType $source, ElementType $dependent): bool
    {
        $types = match ($source) {
            ElementType::Asset => $this->assetDependencyTypes,
            ElementType::Document => $this->documentDependencyTypes,
            ElementType::Object => $this->objectDependencyTypes,
        };

        return $types[$dependent->configKey()] ?? false;
    }
}
