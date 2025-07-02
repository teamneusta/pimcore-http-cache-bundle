<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element\Event;

use Neusta\Pimcore\HttpCacheBundle\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ElementInvalidationEvent extends Event
{
    public bool $cancel = false;

    private function __construct(
        public readonly ElementInterface $element,
        public readonly ElementType $elementType,
        private CacheTags $cacheTags,
    ) {
    }

    public static function fromElement(ElementInterface $element): self
    {
        return new self(
            $element,
            ElementType::fromElement($element),
            CacheTags::fromElement($element),
        );
    }

    public function addTag(CacheTag $tag): void
    {
        $this->cacheTags = $this->cacheTags->with($tag);
    }

    public function addTags(CacheTags $tags): void
    {
        $this->cacheTags = $this->cacheTags->with($tags);
    }

    public function cacheTags(): CacheTags
    {
        return $this->cacheTags;
    }
}
