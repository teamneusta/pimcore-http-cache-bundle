<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheType;

/**
 * @internal
 */
final class EmptyCacheType
{
    public function __construct()
    {
    }

    public function applyTo(string $tag): string
    {
        return $tag;
    }

    public function toString(): string
    {
        return '';
    }
}
