<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Symfony\Contracts\Service\ResetInterface;

final class CacheTagCollectionResponseTagger implements ResponseTagger, ResetInterface
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
        $this->inner->tag($tags);
    }

    public function reset(): void
    {
        $this->collectedTags = new CacheTags();
    }
}
