<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DisableCacheTagCollectionPass implements CompilerPassInterface
{
    /**
     * Disables the decoration of the cache tag collection service if the profiler service is not defined.
     *
     * If the 'profiler' service is absent from the container, this method removes the decoration from
     * the '.neusta_pimcore_http_cache.collect_tags_response_tagger' service.
     *
     * @param ContainerBuilder $container The service container being compiled.
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('profiler')) {
            return;
        }

        $definition = $container->getDefinition(
            '.neusta_pimcore_http_cache.collect_tags_response_tagger',
        );

        $definition->setDecoratedService(null);
    }
}
