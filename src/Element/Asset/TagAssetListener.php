<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element\Asset;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Event\Model\AssetEvent;

final class TagAssetListener
{
    public function __construct(
        private readonly CacheActivator $cacheActivator,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function __invoke(AssetEvent $event): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        $asset = $event->getAsset();

        if ('folder' === $asset->getType()) {
            return;
        }

        $this->cacheTagCollector->addTag(CacheTag::fromElement($asset));
    }
}
