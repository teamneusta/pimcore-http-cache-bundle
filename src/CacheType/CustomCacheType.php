<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheType;

use Neusta\Pimcore\HttpCacheBundle\CacheType;
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

    public function isEmpty(): bool
    {
        return false;
    }
}
