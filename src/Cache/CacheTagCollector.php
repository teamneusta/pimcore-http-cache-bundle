<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use FOS\HttpCache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;

final class CacheTagCollector
{
    private array $collectedTags = [];

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
            $enabledTags = $tags->withoutDisabled($this->tagChecker)->toArray();
            $this->responseTagger->addTags($enabledTags);
            $this->collectedTags = array_merge($this->collectedTags, $enabledTags);
        }
    }

    public function getTags(): array
    {
        return array_unique($this->collectedTags);
    }
}
