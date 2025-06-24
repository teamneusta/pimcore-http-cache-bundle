<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

final class CollectTagsResponseTagger implements ResponseTagger
{
    public readonly CacheTags $collectedTags;

    public function __construct(
        private readonly ResponseTagger $inner,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        $this->collectedTags->addTags($tags);

        $this->inner->tag($tags);
    }
}
