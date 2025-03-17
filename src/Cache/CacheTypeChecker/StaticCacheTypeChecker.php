<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeChecker;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeChecker;

final class StaticCacheTypeChecker implements CacheTypeChecker
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
