<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;

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
