<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache;

use FOS\HttpCache\CacheInvalidator as FosCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;

final class CacheInvalidatorAdapter implements CacheInvalidator
{
    public function __construct(
        private readonly FosCacheInvalidator $invalidator,
    ) {
    }

    public function invalidate(CacheTags $tags): void
    {
        if ($tags->isEmpty()) {
            return;
        }

        $this->invalidator->invalidateTags($tags->toArray());
    }
}
