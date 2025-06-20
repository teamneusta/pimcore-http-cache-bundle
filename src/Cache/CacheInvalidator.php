<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Model\Element\ElementInterface;

final class CacheInvalidator implements CacheInvalidatorInterface
{
    public function __construct(
        private readonly CacheActivator $cacheActivator,
        private readonly CacheTagChecker $tagChecker,
        private readonly InvalidateResponseAdapter $invalidateResponseAdapter,
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

        $tags = $tags->withoutDisabled($this->tagChecker);

        if ($tags->isEmpty()) {
            return;
        }

        $this->invalidateResponseAdapter->invalidate($tags);
    }
}
