<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Neusta\Pimcore\HttpCacheBundle\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\CacheType\EmptyCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Element\ElementInterface;

final class CacheTypeFactory
{
    public static function createEmpty(): EmptyCacheType
    {
        return new EmptyCacheType();
    }

    public static function createFromString(string $type): ElementCacheType|CustomCacheType
    {
        if ($elementType = ElementType::tryFrom($type)) {
            return new ElementCacheType($elementType);
        }

        return new CustomCacheType($type);
    }

    public static function createFromElement(ElementInterface $element): ElementCacheType
    {
        return new ElementCacheType(ElementType::fromElement($element));
    }
}
