<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface CacheType
{
    public function applyTo(string $tag): string;

    public function toString(): string;

    public function isEmpty(): bool;
}
