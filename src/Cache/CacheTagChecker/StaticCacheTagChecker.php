<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;

final class StaticCacheTagChecker implements CacheTagChecker
{
    /**
     * @param array<string, bool> $types
     */
    public function __construct(
        private readonly array $types,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        if ($tag->type->isEmpty()) {
            return true;
        }

        return $this->types[$tag->type->toString()] ?? false;
    }
}
