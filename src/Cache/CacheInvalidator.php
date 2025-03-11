<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Element\ElementInterface;

final class CacheInvalidator implements CacheInvalidatorInterface
{
    public function __construct(
        private readonly CacheActivator $cacheActivator,
        private readonly PurgeCheckerInterface $purgeChecker,
        private readonly CacheManager $cacheManager,
    ) {
    }

    public function invalidateElement(ElementInterface $element, ElementType $type): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        if (!$this->purgeChecker->isEnabled($type->value)) {
            return;
        }

        $this->cacheManager->invalidateTags([CacheTag::fromElement($element)->toString()]);
    }

    public function invalidateElementTags(CacheTags $tags, ElementType $type): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        if (!$this->purgeChecker->isEnabled($type->value)) {
            return;
        }

        if ($tags->isEmpty()) {
            return;
        }

        $this->cacheManager->invalidateTags($tags->toArray());
    }

    public function invalidateTags(CacheTags $tags): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        if ($tags->isEmpty()) {
            return;
        }

        $this->cacheManager->invalidateTags($tags->toArray());
    }
}
