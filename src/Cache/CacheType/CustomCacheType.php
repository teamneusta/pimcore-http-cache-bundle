<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

final class CustomCacheType implements CacheType
{
    public function __construct(
        private readonly string $type,
    ) {
        if ('' === $this->type) {
            throw new \InvalidArgumentException('The cache type must not be empty.');
        }

        if (ElementCacheType::isReserved($type)) {
            throw new \InvalidArgumentException('The given cache type is reserved for Pimcore Elements.');
        }
    }

    public function applyTo(string $tag): string
    {
        return $this->type . '-' . $tag;
    }

    public function toString(): string
    {
        return $this->type;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
