<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheTag;

use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType\CustomCacheTagType;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType\ElementCacheTagType;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType\EmptyCacheTagType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Element\ElementInterface;

final class CacheTagTypeFactory
{
    public static function createEmpty(): EmptyCacheTagType
    {
        return new EmptyCacheTagType();
    }

    public static function createFromString(string $type): ElementCacheTagType|CustomCacheTagType
    {
        if ($elementType = ElementType::tryFrom($type)) {
            return new ElementCacheTagType($elementType);
        }

        return new CustomCacheTagType($type);
    }

    public static function createFromElement(ElementInterface $element): ElementCacheTagType
    {
        return new ElementCacheTagType(ElementType::fromElement($element));
    }
}
