<?php

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\CacheInvalidatorAdapter;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\ResponseTaggerAdapter;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\OnlyWhenActiveCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\RemoveDisabledTagsCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\AssetCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\DocumentCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\ObjectCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\StaticCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\OnlyWhenActiveResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\RemoveDisabledTagsResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use Neusta\Pimcore\HttpCacheBundle\Element\TagElementListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
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

    $services->set(ResponseTagger::class, ResponseTaggerAdapter::class)
        ->arg('$responseTagger', service('fos_http_cache.http.symfony_response_tagger'));

    $services->set(RemoveDisabledTagsResponseTagger::class)
        ->decorate(ResponseTagger::class, null, -99)
        ->args([service('.inner'), service(CacheTagChecker::class)]);

    $services->set(OnlyWhenActiveResponseTagger::class)
        ->decorate(ResponseTagger::class, null, -100)
        ->args([service('.inner'), service(CacheActivator::class)]);

    $services->set(StaticCacheTagChecker::class)
        ->arg('$types', abstract_arg('Set in the extension'));

    $services->set(ElementCacheTagChecker::class)
        ->decorate(StaticCacheTagChecker::class)
        ->arg('$inner', service('.inner'))
        ->arg('$asset', service(AssetCacheTagChecker::class))
        ->arg('$document', service(DocumentCacheTagChecker::class))
        ->arg('$object', service(ObjectCacheTagChecker::class));

    $services->set(ElementRepository::class);

    $services->set(AssetCacheTagChecker::class)
        ->arg('$repository', service(ElementRepository::class))
        ->arg('$config', ['enabled' => false, 'types' => []]);

    $services->set(DocumentCacheTagChecker::class)
        ->arg('$repository', service(ElementRepository::class))
        ->arg('$config', ['enabled' => false, 'types' => []]);

    $services->set(ObjectCacheTagChecker::class)
        ->arg('$repository', service(ElementRepository::class))
        ->arg('$config', ['enabled' => false, 'types' => [], 'classes' => []]);

    $services->alias(CacheTagChecker::class, StaticCacheTagChecker::class);

    $services->set(TagElementListener::class)
        ->arg('$responseTagger', service(ResponseTagger::class))
        ->arg('$dispatcher', service('event_dispatcher'));

    $services->set(InvalidateElementListener::class)
        ->arg('$cacheInvalidator', service(CacheInvalidator::class))
        ->arg('$dispatcher', service('event_dispatcher'));
};
