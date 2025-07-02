<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\ResponseTagger;

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
