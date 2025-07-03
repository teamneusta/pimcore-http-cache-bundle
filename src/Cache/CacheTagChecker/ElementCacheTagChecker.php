<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;

final class ElementCacheTagChecker implements CacheTagChecker
{
    public function __construct(
        private readonly CacheTagChecker $inner,
        private readonly CacheTagChecker $asset,
        private readonly CacheTagChecker $document,
        private readonly CacheTagChecker $object,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        if (!$tag->type instanceof ElementCacheType) {
            return $this->inner->isEnabled($tag);
        }

        return match ($tag->type->type) {
            ElementType::Asset => $this->asset->isEnabled($tag),
            ElementType::Document => $this->document->isEnabled($tag),
            ElementType::Object => $this->object->isEnabled($tag),
        };
    }
}
