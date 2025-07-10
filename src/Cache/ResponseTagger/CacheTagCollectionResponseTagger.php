<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

final class CacheTagCollectionResponseTagger implements ResponseTagger
{
    public CacheTags $collectedTags;

    /**
     * Initializes the CacheTagCollectionResponseTagger with an inner ResponseTagger and an empty collection of cache tags.
     *
     * @param ResponseTagger $inner The wrapped ResponseTagger instance to which tagging operations are delegated.
     */
    public function __construct(
        private readonly ResponseTagger $inner,
    ) {
        $this->collectedTags = new CacheTags();
    }

    /**
     * Merges the given cache tags into the internal collection and delegates tagging to the wrapped response tagger.
     *
     * @param CacheTags $tags The cache tags to add to the collection.
     */
    public function tag(CacheTags $tags): void
    {
        $this->collectedTags = $this->collectedTags->with($tags);
        $this->inner->tag($this->collectedTags);
    }
}
