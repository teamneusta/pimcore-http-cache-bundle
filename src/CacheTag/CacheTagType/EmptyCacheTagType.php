<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType;

use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType;

final class EmptyCacheTagType implements CacheTagType
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
