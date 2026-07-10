<?php declare(strict_types=1);

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\CacheInvalidatorAdapter;
use Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache\ResponseTaggerAdapter;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\OnlyWhenInvalidatingCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\RemoveDisabledTagsCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\AssetCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\DocumentCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\ObjectCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\StaticCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\OnlyWhenCollectingResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\RemoveDisabledTagsResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\TraceableResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Neusta\Pimcore\HttpCacheBundle\DataCollector;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use Neusta\Pimcore\HttpCacheBundle\Element\TagElementListener;
use Neusta\Pimcore\HttpCacheBundle\EventListener\ConsoleCacheScopeListener;
use Neusta\Pimcore\HttpCacheBundle\EventListener\HttpCacheScopeListener;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set('neusta_pimcore_http_cache.cache_scope', CacheActivator::class)
        ->tag('kernel.reset', ['method' => 'reset'])
        ->alias(CacheScope::class, 'neusta_pimcore_http_cache.cache_scope');

    $services->alias('neusta_pimcore_http_cache.cache_activator', 'neusta_pimcore_http_cache.cache_scope')
        ->deprecate('teamneusta/pimcore-http-cache-bundle', '0.8', 'The "%alias_id%" alias is deprecated, use "neusta_pimcore_http_cache.cache_scope" instead.')
        ->alias(CacheActivator::class, 'neusta_pimcore_http_cache.cache_activator')
        ->deprecate('teamneusta/pimcore-http-cache-bundle', '0.8', 'The "%alias_id%" alias is deprecated, use "' . CacheScope::class . '" instead.');

    $services->set('neusta_pimcore_http_cache.cache_scope.console_listener', ConsoleCacheScopeListener::class)
        ->arg('$cacheScope', service('neusta_pimcore_http_cache.cache_scope'))
        ->autoconfigure();

    $services->set('neusta_pimcore_http_cache.cache_scope.http_listener', HttpCacheScopeListener::class)
        ->arg('$cacheScope', service('neusta_pimcore_http_cache.cache_scope'))
        ->arg('$responseTagger', service('neusta_pimcore_http_cache.response_tagger'))
        ->arg('$contextResolver', service(PimcoreContextResolver::class))
        ->arg('$documentResolver', service(DocumentResolver::class))
        ->arg('$collectFromRequest', abstract_arg('Set in the extension'))
        ->arg('$tagFallbackDocument', abstract_arg('Set in the extension'))
        ->autoconfigure();

    $services->set('neusta_pimcore_http_cache.cache_invalidator', CacheInvalidatorAdapter::class)
        ->arg('$invalidator', service(CacheManager::class));

    $services->set(null, RemoveDisabledTagsCacheInvalidator::class)
        ->decorate('neusta_pimcore_http_cache.cache_invalidator', null, -99)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_tag_checker')]);

    $services->set(null, OnlyWhenInvalidatingCacheInvalidator::class)
        ->decorate('neusta_pimcore_http_cache.cache_invalidator', null, -100)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_scope')]);

    $services->alias(CacheInvalidator::class, 'neusta_pimcore_http_cache.cache_invalidator');

    $services->set('neusta_pimcore_http_cache.response_tagger', ResponseTaggerAdapter::class)
        ->arg('$responseTagger', service('fos_http_cache.http.symfony_response_tagger'));

    $services->set(null, RemoveDisabledTagsResponseTagger::class)
        ->decorate('neusta_pimcore_http_cache.response_tagger', null, -99)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_tag_checker')]);

    $services->set(null, OnlyWhenCollectingResponseTagger::class)
        ->decorate('neusta_pimcore_http_cache.response_tagger', null, -100)
        ->args([service('.inner'), service('neusta_pimcore_http_cache.cache_scope')]);

    $services->set('.neusta_pimcore_http_cache.response_tagger.traceable', TraceableResponseTagger::class)
        ->decorate('neusta_pimcore_http_cache.response_tagger', null, 100)
        ->args([service('.inner')])
        ->tag('kernel.reset', ['method' => 'reset']);

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
        ->arg('$traceableResponseTagger', service('.neusta_pimcore_http_cache.response_tagger.traceable'))
        ->arg('$configuration', param('neusta_pimcore_http_cache.config'))
        ->tag('data_collector', [
            'template' => '@NeustaPimcoreHttpCache/profiler.html.twig',
            'id' => 'pimcore_http_cache',
            'priority' => 255,
        ]);
};
