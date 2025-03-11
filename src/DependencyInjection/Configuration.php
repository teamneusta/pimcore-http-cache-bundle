<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neusta_pimcore_http_cache');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('asset')->defaultTrue()->end()
                ->booleanNode('document')->defaultTrue()->end()
                ->booleanNode('object')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
