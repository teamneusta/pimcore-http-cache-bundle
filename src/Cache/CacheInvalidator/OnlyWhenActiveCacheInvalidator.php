<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;

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
