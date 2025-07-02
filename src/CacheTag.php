<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType\ElementCacheTagType;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagTypeFactory;
use Neusta\Pimcore\HttpCacheBundle\Exception\InvalidArgumentException;
use Pimcore\Model\Element\ElementInterface;

final class CacheTag
{
    private function __construct(
        public readonly string $tag,
        public readonly CacheTagType $type,
    ) {
        if ('' === trim($tag)) {
            throw InvalidArgumentException::becauseCacheTagIsEmpty();
        }

        if (!$type instanceof ElementCacheTagType && ElementCacheTagType::isReserved($type->toString())) {
            throw InvalidArgumentException::becauseCacheTypeIsReserved($type);
        }
    }

    public static function fromString(string $tag, ?CacheTagType $type = null): self
    {
        return new self($tag, $type ?? CacheTagTypeFactory::createEmpty());
    }

    public static function fromElement(ElementInterface $element): self
    {
        if (!$id = $element->getId()) {
            throw InvalidArgumentException::becauseElementHasNoId();
        }

        return new self((string) $id, CacheTagTypeFactory::createFromElement($element));
    }

    public function toString(): string
    {
        return $this->type->applyTo($this->tag);
    }
}
