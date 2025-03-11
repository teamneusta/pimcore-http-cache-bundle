<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Element\ElementInterface;

interface CacheInvalidatorInterface
{
    public function invalidateElement(ElementInterface $element, ElementType $type): void;

    public function invalidateElementTags(CacheTags $tags, ElementType $type): void;

    public function invalidateTags(CacheTags $tags): void;
}
