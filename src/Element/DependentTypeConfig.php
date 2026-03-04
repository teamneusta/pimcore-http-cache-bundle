<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

final readonly class DependentTypeConfig
{
    public function __construct(
        private bool $enabled,
        /** @var array<string, bool> */
        private array $types,
        /** @var array<string, bool> */
        private array $classes,
    ) {
    }

    /** @param array<mixed>|bool $config */
    public static function fromArray(array|bool $config): self
    {
        if (is_bool($config)) {
            return new self(enabled: $config, types: [], classes: []);
        }

        return new self(
            enabled: $config['enabled'] ?? false,
            types: $config['types'] ?? [],
            classes: $config['classes'] ?? [],
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isTypeEnabled(string $type): bool
    {
        return $this->types[$type] ?? true;
    }

    public function isObjectClassEnabled(string $class): bool
    {
        return $this->classes[$class] ?? true;
    }
}
