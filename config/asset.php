<?php

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\Asset\TagAssetListener;
use Pimcore\Event\AssetEvents;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(TagAssetListener::class)
        ->arg('$cacheActivator', service(CacheActivator::class))
        ->arg('$cacheTagCollector', service(CacheTagCollector::class))
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_LOAD]);
};
