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
            ->fixXmlConfig('cache_type')
            ->children()
                ->arrayNode('elements')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('asset')
                    ->fixXmlConfig('document')
                    ->fixXmlConfig('object')
                    ->children()
                        ->arrayNode('assets')
                            ->fixXmlConfig('type')
                            ->canBeDisabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('types')
                                    ->info('Enable/disable cache handling for asset types.')
                                    ->normalizeKeys(false)
                                    ->useAttributeAsKey('type')
                                    ->defaultValue(['folder' => false])
                                    ->booleanPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('documents')
                            ->fixXmlConfig('type')
                            ->canBeDisabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('types')
                                    ->info('Enable/disable cache handling for document types.')
                                    ->normalizeKeys(false)
                                    ->useAttributeAsKey('type')
                                    ->defaultValue(['email' => false, 'folder' => false, 'hardlink' => false])
                                    ->booleanPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('objects')
                            ->fixXmlConfig('type')
                            ->fixXmlConfig('class')
                            ->canBeDisabled()
                            ->canBeDisabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('types')
                                    ->info('Enable/disable cache handling for data object types.')
                                    ->normalizeKeys(false)
                                    ->useAttributeAsKey('type')
                                    ->defaultValue(['folder' => false])
                                    ->booleanPrototype()->end()
                                ->end()
                                ->arrayNode('classes')
                                    ->info('Enable/disable cache handling for data object classes.')
                                    ->normalizeKeys(false)
                                    ->useAttributeAsKey('class')
                                    ->defaultValue([])
                                    ->booleanPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache_types')
                    ->info('Enable/disable cache handling for custom cache types.')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('type')
                    ->booleanPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
