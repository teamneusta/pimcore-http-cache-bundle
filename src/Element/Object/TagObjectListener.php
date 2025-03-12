<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element\Object;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Event\Model\DataObjectEvent;

final class TagObjectListener
{
    public function __construct(
        private readonly CacheTagCollector $cacheTagCollector,
        private readonly CacheActivator $cacheActivator,
    ) {
    }

    public function __invoke(DataObjectEvent $event): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        $object = $event->getObject();

        if ('folder' === $object->getType()) {
            return;
        }

        $this->cacheTagCollector->addTag(CacheTag::fromElement($object));
    }
}
