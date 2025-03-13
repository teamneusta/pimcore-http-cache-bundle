<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

final class StaticCacheTypeChecker implements CacheTypeChecker
{
    /**
     * @param array<string, bool> $types
     */
    public function __construct(
        private readonly array $types,
    ) {
    }

    public function isEnabled(CacheType $type): bool
    {
        return $this->types[$type->toString()] ?? false;
    }
}
