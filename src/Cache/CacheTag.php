<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;

final class CacheTag
{
    private function __construct(
        private readonly string $tag,
        private readonly CacheType $type,
    ) {
        if ('' === trim($tag)) {
            throw new \InvalidArgumentException('The cache tag must not be empty.');
        }
    }

    public static function fromString(string $tag, ?CacheType $elementType = null): self
    {
        return new self($tag, $elementType ?? CacheType::empty());
    }

    public static function fromElement(ElementInterface $element): self
    {
        if (!$id = $element->getId()) {
            throw new \InvalidArgumentException('The given element has no id.');
        }

        return new self((string) $id, CacheType::fromElement($element));
    }

    public function isEnabled(CacheTypeChecker $checker): bool
    {
        return $this->type->isEnabled($checker);
    }

    public function toString(): string
    {
        return $this->type->applyTo($this->tag);
    }
}
