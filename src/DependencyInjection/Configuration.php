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
            ->fixXmlConfig('element')
            ->children()
                ->arrayNode('elements')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('asset')
                    ->fixXmlConfig('document')
                    ->fixXmlConfig('object')
                    ->children()
                        ->booleanNode('assets')->defaultTrue()->end()
                        ->booleanNode('documents')->defaultTrue()->end()
                        ->booleanNode('objects')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
