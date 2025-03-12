<?php

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidationListener;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidatorInterface;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\Cache\PurgeChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\PurgeCheckerInterface;
use Neusta\Pimcore\HttpCacheBundle\Cache\PurgeCheckerInterface;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(CacheActivator::class);

    $services->set(CacheInvalidatorInterface::class, CacheInvalidator::class)
        ->arg('$cacheActivator', service(CacheActivator::class))
        ->arg('$purgeChecker', service(PurgeCheckerInterface::class))
        ->arg('$cacheManager', service(CacheManager::class));

    $services->set(CacheTagCollector::class)
        ->arg('$responseTagger', service('fos_http_cache.http.symfony_response_tagger'));

    $services->set(PurgeCheckerInterface::class, PurgeChecker::class)
        ->arg('$types', abstract_arg('Set in the extension'));

    $services->set(CacheInvalidationListener::class)
        ->arg('$cacheManager', service(CacheManager::class))
        ->arg('$logger',  service('logger'))
        ->tag('kernel.event_listener', ['event' => WorkerMessageHandledEvent::class]);

    $services->set(InvalidateElementListener::class)
        ->arg('$cacheInvalidator', service(CacheInvalidatorInterface::class))
        ->arg('$dispatcher', service('event_dispatcher'))
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE, 'method' => 'onUpdated'])
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_DELETE, 'method' => 'onDeleted'])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE, 'method' => 'onUpdated'])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_DELETE, 'method' => 'onDeleted'])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE, 'method' => 'onUpdated'])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_DELETE, 'method' => 'onDeleted']);
};
