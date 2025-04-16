<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use FOS\HttpCache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;

final class CacheTagCollector
{
    public function __construct(
        private readonly CacheActivator $activator,
        private readonly CacheTagChecker $tagChecker,
        private readonly ResponseTagger $responseTagger,
    ) {
    }

    public function addTag(CacheTag $tag): void
    {
        $this->addTags(new CacheTags($tag));
    }

    public function addTags(CacheTags $tags): void
    {
        if ($this->activator->isCachingActive()) {
            $this->responseTagger->addTags($tags->withoutDisabled($this->tagChecker)->toArray());
        }
    }
}
