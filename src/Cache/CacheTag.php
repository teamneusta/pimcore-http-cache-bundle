<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Exception\InvalidArgumentException;
use Pimcore\Model\Element\ElementInterface;

final class CacheTag
{
    private function __construct(
        public readonly string $tag,
        public readonly CacheType $type,
    ) {
        if ('' === trim($tag)) {
            throw InvalidArgumentException::becauseEmptyCacheTypeIsNotAllowed();
        }

        if (!$type instanceof ElementCacheType && ElementCacheType::isReserved($type->toString())) {
            throw InvalidArgumentException::becauseCacheTypeIsReserved($type->toString());
        }
    }

    public static function fromString(string $tag, ?CacheType $type = null): self
    {
        return new self($tag, $type ?? CacheTypeFactory::createEmpty());
    }

    public static function fromElement(ElementInterface $element): self
    {
        if (!$id = $element->getId()) {
            throw InvalidArgumentException::becauseElementHasNoId();
        }

        return new self((string) $id, CacheTypeFactory::createFromElement($element));
    }

    public function toString(): string
    {
        return $this->type->applyTo($this->tag);
    }
}
