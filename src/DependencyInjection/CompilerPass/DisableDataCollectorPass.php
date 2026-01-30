<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DisableDataCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('profiler')) {
            $container->removeDefinition('.neusta_pimcore_http_cache.response_tagger.traceable');
            $container->removeDefinition('neusta_pimcore_http_cache.data_collector');
        }
    }
}
