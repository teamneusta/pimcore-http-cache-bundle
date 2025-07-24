<?php

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\CacheInvalidatorAdapter;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\ResponseTaggerAdapter;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\OnlyWhenActiveCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\RemoveDisabledTagsCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\AssetCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\DocumentCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\ObjectCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\StaticCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CacheTagCollectionResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\OnlyWhenActiveResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\RemoveDisabledTagsResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\DataCollector;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use Neusta\Pimcore\HttpCacheBundle\Element\TagElementListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set('neusta_pimcore_http_cache.cache_activator', CacheActivator::class)
        ->alias(CacheActivator::class, 'neusta_pimcore_http_cache.cache_activator');

    $services->set('neusta_pimcore_http_cache.cache_invalidator', CacheInvalidatorAdapter::class)
        ->arg('$invalidator', service(CacheManager::class));

    $services->set(null, RemoveDisabledTagsCacheInvalidator::class)
        ->decorate('neusta_pimcore_http_cache.cache_invalidator', null, -99)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_tag_checker')]);

    $services->set(null, OnlyWhenActiveCacheInvalidator::class)
        ->decorate('neusta_pimcore_http_cache.cache_invalidator', null, -100)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_activator')]);

    $services->alias(CacheInvalidator::class, 'neusta_pimcore_http_cache.cache_invalidator');

    $services->set('neusta_pimcore_http_cache.response_tagger', ResponseTaggerAdapter::class)
        ->arg('$responseTagger', service('fos_http_cache.http.symfony_response_tagger'));

    $services->set(null, RemoveDisabledTagsResponseTagger::class)
        ->decorate('neusta_pimcore_http_cache.response_tagger', null, -99)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_tag_checker')]);

    $services->set(null, OnlyWhenActiveResponseTagger::class)
        ->decorate('neusta_pimcore_http_cache.response_tagger', null, -100)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_activator')]);

    $services->set('.neusta_pimcore_http_cache.collect_tags_response_tagger', CacheTagCollectionResponseTagger::class)
        ->decorate('neusta_pimcore_http_cache.response_tagger', null, 1)
        ->args([service('.inner')]);

    $services->alias(ResponseTagger::class, 'neusta_pimcore_http_cache.response_tagger');

    $services->set('neusta_pimcore_http_cache.cache_tag_checker', StaticCacheTagChecker::class)
        ->arg('$types', abstract_arg('Set in the extension'));

    $services->set('neusta_pimcore_http_cache.cache_tag_checker.element', ElementCacheTagChecker::class)
        ->decorate('neusta_pimcore_http_cache.cache_tag_checker')
        ->arg('$inner', service('.inner'))
        ->arg('$asset', service('neusta_pimcore_http_cache.cache_tag_checker.element.asset'))
        ->arg('$document', service('neusta_pimcore_http_cache.cache_tag_checker.element.document'))
        ->arg('$object', service('neusta_pimcore_http_cache.cache_tag_checker.element.object'));

    $services->set('.neusta_pimcore_http_cache.element.repository', ElementRepository::class);

    $services->set('neusta_pimcore_http_cache.cache_tag_checker.element.asset', AssetCacheTagChecker::class)
        ->arg('$repository', service('.neusta_pimcore_http_cache.element.repository'))
        ->arg('$config', ['enabled' => false, 'types' => []]);

    $services->set('neusta_pimcore_http_cache.cache_tag_checker.element.document', DocumentCacheTagChecker::class)
        ->arg('$repository', service('.neusta_pimcore_http_cache.element.repository'))
        ->arg('$config', ['enabled' => false, 'types' => []]);

    $services->set('neusta_pimcore_http_cache.cache_tag_checker.element.object', ObjectCacheTagChecker::class)
        ->arg('$repository', service('.neusta_pimcore_http_cache.element.repository'))
        ->arg('$config', ['enabled' => false, 'types' => [], 'classes' => []]);

    $services->set('neusta_pimcore_http_cache.element.tag_listener', TagElementListener::class)
        ->arg('$responseTagger', service('neusta_pimcore_http_cache.response_tagger'))
        ->arg('$dispatcher', service('event_dispatcher'));

    $services->set('neusta_pimcore_http_cache.element.invalidate_listener', InvalidateElementListener::class)
        ->arg('$cacheInvalidator', service('neusta_pimcore_http_cache.cache_invalidator'))
        ->arg('$dispatcher', service('event_dispatcher'));

    $services->set('neusta_pimcore_http_cache.data_collector', DataCollector::class)
        ->arg('$cacheTagCollector', service('.neusta_pimcore_http_cache.collect_tags_response_tagger'))
        ->arg('$configuration', param('.neusta_pimcore_http_cache.config'))
        ->tag('data_collector', [
            'template' => '@NeustaPimcoreHttpCache/profiler.html.twig',
            'id' => 'pimcore_http_cache.data_collector',
            'priority' => 255,
        ]);
};
