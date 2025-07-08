<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

final class CollectTagsResponseTagger implements ResponseTagger
{
    public CacheTags $collectedTags;

    public function __construct(
        private readonly ResponseTagger $inner,
    ) {
        $this->collectedTags = new CacheTags();
    }

    public function tag(CacheTags $tags): void
    {
        $this->collectedTags = $this->collectedTags->with($tags);
        $this->inner->tag($this->collectedTags);
    }
}
