<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
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

        if (!$type instanceof ElementCacheType && ElementCacheType::isReserved($type->toString())) {
            throw new \InvalidArgumentException(\sprintf(
                'The cache type "%s" is reserved for Pimcore elements.',
                $type->toString(),
            ));
        }
    }

    public static function fromString(string $tag, ?CacheType $type = null): self
    {
        return new self($tag, $type ?? CacheTypeFactory::createEmpty());
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
