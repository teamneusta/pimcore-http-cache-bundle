<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;

final class CacheTag
{
    private function __construct(
        public readonly string $tag,
        public readonly CacheType $type,
    ) {
        if ('' === trim($tag)) {
            throw new \InvalidArgumentException('The cache tag must not be empty.');
        }
    }

    public static function fromString(string $tag, ?CacheType $elementType = null): self
    {
        return new self($tag, $elementType ?? CacheTypeFactory::createEmpty());
    }

    public static function fromElement(ElementInterface $element): self
    {
        if (!$id = $element->getId()) {
            throw new \InvalidArgumentException('The given element has no id.');
        }

        return new self((string) $id, CacheTypeFactory::createFromElement($element));
    }

    public function toString(): string
    {
        return $this->type->applyTo($this->tag);
    }
}
