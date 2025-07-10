<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface CacheType
{
    public function applyTo(string $tag): string;

    /**
 * Returns a string representation of the cache type.
 *
 * @return string The cache type as a string.
 */
public function toString(): string;

    /**
 * Determines whether the cache type is empty.
 *
 * @return bool True if the cache type is empty, false otherwise.
 */
public function isEmpty(): bool;

    /**
 * Returns a unique string identifier for the cache type.
 *
 * @return string The cache type identifier.
 */
public function identifier(): string;
}
