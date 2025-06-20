<?php

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\CacheInvalidatorAdapter;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidationListener;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\OnlyWhenActiveCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\RemoveDisabledTagsCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\StaticCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use Neusta\Pimcore\HttpCacheBundle\Element\TagElementListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(CacheActivator::class);

    $services->set(CacheInvalidator::class, CacheInvalidatorAdapter::class)
        ->arg('$invalidator', service(CacheManager::class));

    $services->set(RemoveDisabledTagsCacheInvalidator::class)
        ->decorate(CacheInvalidator::class, null, -99)
        ->args([service('.inner'), service(CacheTagChecker::class)]);

    $services->set(OnlyWhenActiveCacheInvalidator::class)
        ->decorate(CacheInvalidator::class, null, -100)
        ->args([service('.inner'), service(CacheActivator::class)]);

    $services->set(CacheTagCollector::class)
        ->arg('$activator', service(CacheActivator::class))
        ->arg('$tagChecker', service(CacheTagChecker::class))
        ->arg('$responseTagger', service('fos_http_cache.http.symfony_response_tagger'));

    $services->set(StaticCacheTagChecker::class)
        ->arg('$types', abstract_arg('Set in the extension'));

    $services->set(ElementCacheTagChecker::class)
        ->decorate(StaticCacheTagChecker::class)
        ->arg('$inner', service('.inner'))
        ->arg('$repository', inline_service(ElementRepository::class))
        ->arg('$assets', ['enabled' => false, 'types' => []])
        ->arg('$documents', ['enabled' => false, 'types' => []])
        ->arg('$objects', ['enabled' => false, 'types' => [], 'classes' => []]);

    $services->alias(CacheTagChecker::class, StaticCacheTagChecker::class);

    $services->set(TagElementListener::class)
        ->arg('$tagCollector', service(CacheTagCollector::class))
        ->arg('$dispatcher', service('event_dispatcher'));

    $services->set(CacheInvalidationListener::class)
        ->arg('$invalidator', service(CacheManager::class))
        ->arg('$logger',  service('logger'))
        ->tag('kernel.event_listener', ['event' => WorkerMessageHandledEvent::class]);

    $services->set(InvalidateElementListener::class)
        ->arg('$cacheInvalidator', service(CacheInvalidator::class))
        ->arg('$dispatcher', service('event_dispatcher'));
};
