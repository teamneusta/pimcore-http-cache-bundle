<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;

final class RemoveDisabledTagsCacheInvalidator implements CacheInvalidator
{
    public function __construct(
        private readonly CacheInvalidator $inner,
        private readonly CacheTagChecker $tagChecker,
    ) {
    }

    public function invalidate(CacheTags $tags): void
    {
        $this->inner->invalidate($tags->withoutDisabled($this->tagChecker));
    }
}
