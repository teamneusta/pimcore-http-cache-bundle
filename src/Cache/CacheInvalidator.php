<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use FOS\HttpCache\CacheInvalidator as FosCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Model\Element\ElementInterface;

final class CacheInvalidator implements CacheInvalidatorInterface
{
    public function __construct(
        private readonly CacheActivator $cacheActivator,
        private readonly CacheTypeChecker $typeChecker,
        private readonly FosCacheInvalidator $invalidator,
    ) {
    }

    public function invalidateElement(ElementInterface $element): void
    {
        $this->invalidateTags(CacheTags::fromElements([$element]));
    }

    public function invalidateTags(CacheTags $tags): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        $tags = $tags->onlyEnabled($this->typeChecker);

        if ($tags->isEmpty()) {
            return;
        }

        $this->invalidator->invalidateTags($tags->toArray());
    }
}
