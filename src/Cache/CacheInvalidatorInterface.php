<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;

interface CacheInvalidatorInterface
{
    public function invalidateElement(ElementInterface $element): void;

    public function invalidateTags(CacheTags $tags): void;
}
