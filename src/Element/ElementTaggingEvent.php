<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ElementTaggingEvent extends Event
{
    public bool $cancel = false;

    private function __construct(
        public readonly ElementInterface $element,
        public readonly ElementType $elementType,
        public readonly CacheTags $cacheTags,
    ) {
    }

    public static function fromElement(ElementInterface $element): self
    {
        return new self(
            $element,
            ElementType::fromElement($element),
            CacheTags::fromElements([$element]),
        );
    }
}
