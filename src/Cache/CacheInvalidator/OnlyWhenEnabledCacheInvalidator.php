<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;

final class OnlyWhenEnabledCacheInvalidator implements CacheInvalidator
{
    public function __construct(
        private readonly CacheInvalidator $inner,
        private readonly CacheScope $cacheScope,
    ) {
    }

    public function invalidate(CacheTags $tags): void
    {
        if ($this->cacheScope->isEnabled()) {
            $this->inner->invalidate($tags);
        }
    }
}
