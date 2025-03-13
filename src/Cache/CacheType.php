<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\EmptyCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Element\ElementInterface;

final class CacheType
{
    private function __construct(
        private readonly EmptyCacheType|ElementCacheType|CustomCacheType $type,
    ) {
    }

    public static function empty(): self
    {
        return new self(new EmptyCacheType());
    }

    public static function fromString(string $type): self
    {
        if (ElementCacheType::isReserved($type)) {
            throw new \InvalidArgumentException('The given cache type is reserved for Pimcore Elements.');
        }

        if ($elementType = ElementType::tryFrom($type)) {
            return new self(new ElementCacheType($elementType));
        }

        return new self(new CustomCacheType($type));
    }

    public static function fromElement(ElementInterface $element): self
    {
        return new self(new ElementCacheType(ElementType::fromElement($element)));
    }

    public function isEnabled(CacheTypeChecker $checker): bool
    {
        return $this->type instanceof EmptyCacheType || $checker->isEnabled($this);
    }

    public function applyTo(string $tag): string
    {
        return $this->type->applyTo($tag);
    }

    public function toString(): string
    {
        return $this->type->toString();
    }
}
