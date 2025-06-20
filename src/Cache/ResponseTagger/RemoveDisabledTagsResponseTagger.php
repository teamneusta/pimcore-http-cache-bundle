<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

final class RemoveDisabledTagsResponseTagger implements ResponseTagger
{
    public function __construct(
        private readonly ResponseTagger $inner,
        private readonly CacheTagChecker $tagChecker,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        $this->inner->tag($tags->withoutDisabled($this->tagChecker));
    }
}
