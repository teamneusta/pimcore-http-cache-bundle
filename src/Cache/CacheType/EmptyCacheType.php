<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

final class EmptyCacheType implements CacheType
{
    public function applyTo(string $tag): string
    {
        return $tag;
    }

    public function toString(): string
    {
        return '';
    }

    public function isEmpty(): bool
    {
        return true;
    }
}
