<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

/**
 * @internal
 */
final class CustomCacheType
{
    public function __construct(
        private readonly string $type,
    ) {
    }

    public function applyTo(string $tag): string
    {
        return $this->type . '-' . $tag;
    }

    public function toString(): string
    {
        return $this->type;
    }
}
