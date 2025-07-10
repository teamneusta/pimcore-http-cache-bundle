<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;
use Neusta\Pimcore\HttpCacheBundle\Exception\InvalidArgumentException;

final class CustomCacheType implements CacheType
{
    public function __construct(
        private readonly string $type,
    ) {
        if ('' === $this->type) {
            throw InvalidArgumentException::becauseCacheTypeIsEmpty();
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

    /**
     * Indicates whether the cache type is empty.
     *
     * @return bool Always returns false.
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Returns the identifier for this cache type.
     *
     * @return string The cache type identifier.
     */
    public function identifier(): string
    {
        return $this->type;
    }
}
