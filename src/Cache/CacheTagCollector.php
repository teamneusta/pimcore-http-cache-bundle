<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use FOS\HttpCache\ResponseTagger;

final class CacheTagCollector
{
    public function __construct(
        private readonly ResponseTagger $responseTagger,
    ) {
    }

    public function addTag(CacheTag $tag): void
    {
        $this->addTags(new CacheTags($tag));
    }

    public function addTags(CacheTags $tags): void
    {
        $this->responseTagger->addTags($tags->toArray());
    }
}
