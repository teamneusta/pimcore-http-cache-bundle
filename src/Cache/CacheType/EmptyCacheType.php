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

    /**
     * Indicates that this cache type is empty.
     *
     * @return bool Always returns true.
     */
    public function isEmpty(): bool
    {
        return true;
    }

    /**
     * Returns the identifier for this cache type.
     *
     * @return string The string 'empty'.
     */
    public function identifier(): string
    {
        return 'empty';
    }
}
