<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;

final class OnlyWhenCollectingResponseTagger implements ResponseTagger
{
    public function __construct(
        private readonly ResponseTagger $inner,
        private readonly CacheScope $cacheScope,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        if ($this->cacheScope->isCollecting()) {
            $this->inner->tag($tags);
        }
    }
}
