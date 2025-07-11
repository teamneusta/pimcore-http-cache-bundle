<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DisableCacheTagCollectionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('profiler')) {
            $container->removeDefinition('.neusta_pimcore_http_cache.collect_tags_response_tagger');
            $container->removeDefinition('neusta_pimcore_http_cache.cache.data_collector.cache_tag_data_collector');
        }
    }
}
