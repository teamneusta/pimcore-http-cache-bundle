<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\ResponseTagger;

final class OnlyWhenActiveResponseTagger implements ResponseTagger
{
    public function __construct(
        private readonly ResponseTagger $inner,
        private readonly CacheActivator $cacheActivator,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        if ($this->cacheActivator->isCachingActive()) {
            $this->inner->tag($tags);
        }
    }
}
