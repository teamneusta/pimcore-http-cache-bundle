<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;

final class OnlyWhenActiveCacheInvalidator implements CacheInvalidator
{
    public function __construct(
        private readonly CacheInvalidator $inner,
        private readonly CacheActivator $cacheActivator,
    ) {
    }

    public function invalidate(CacheTags $tags): void
    {
        if ($this->cacheActivator->isCachingActive()) {
            $this->inner->invalidate($tags);
        }
    }
}
