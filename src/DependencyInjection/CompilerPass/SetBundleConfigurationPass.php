<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SetBundleConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('profiler')) {
            $definition = $container->getDefinition('neusta_pimcore_http_cache.cache.data_collector.configuration_collector');
            $configuration = $container->getParameter('neusta_pimcore_http_cache.config');
            $definition->setArgument(0, $configuration);
        }
    }
}
