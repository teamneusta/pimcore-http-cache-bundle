<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Event\Model\ElementEventInterface;

final class TagElementListener
{
    public function __construct(
        private readonly CacheActivator $activator,
        private readonly CacheTagChecker $tagChecker,
        private readonly CacheTagCollector $tagCollector,
    ) {
    }

    public function __invoke(ElementEventInterface $event): void
    {
        if (!$this->activator->isCachingActive()) {
            return;
        }

        $tag = CacheTag::fromElement($event->getElement());

        if ($this->tagChecker->isEnabled($tag)) {
            $this->tagCollector->addTag($tag);
        }
    }
}
