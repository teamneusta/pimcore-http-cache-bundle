<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DisableCacheTagCollectionPass implements CompilerPassInterface
{
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
