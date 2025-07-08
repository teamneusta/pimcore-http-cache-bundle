<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CollectTagsResponseTagger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CacheTagCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('profiler')) {
            return;
        }

        $definition = $container->getDefinition(CollectTagsResponseTagger::class);
        $definition->setDecoratedService(null);
    }
}
