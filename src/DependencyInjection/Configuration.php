<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                                    ->beforeNormalization()
                                        ->always(fn ($v) => $v + ['folder' => false])
                                    ->end()
                                    ->normalizeKeys(false)
                                    ->useAttributeAsKey('type')
                                    ->defaultValue(['folder' => false])
                                    ->booleanPrototype()->end()
                                ->end()
                                ->arrayNode('invalidate_dependent_elements')
                                    ->info('Enable/disable invalidation of dependent elements when an asset is updated or deleted.')
                                    ->canBeEnabled()
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('types')
                                            ->info('Enable/disable invalidation of dependent element types.')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->append($this->buildDependentTypeNode('assets'))
                                                ->append($this->buildDependentTypeNode('documents'))
                                                ->append($this->buildDependentTypeNode('objects', withClasses: true))
                                            ->end()
                                        ->end()
                                    ->end()
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
                                    ->beforeNormalization()
                                        ->always(fn ($v) => $v + ['email' => false, 'folder' => false, 'hardlink' => false])
                                    ->end()
                                    ->normalizeKeys(false)
                                    ->useAttributeAsKey('type')
                                    ->defaultValue(['email' => false, 'folder' => false, 'hardlink' => false])
                                    ->booleanPrototype()->end()
                                ->end()
                                ->arrayNode('invalidate_dependent_elements')
                                    ->info('Enable/disable invalidation of dependent elements when a document is updated or deleted.')
                                    ->canBeEnabled()
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('types')
                                            ->info('Enable/disable invalidation of dependent element types.')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->append($this->buildDependentTypeNode('assets'))
                                                ->append($this->buildDependentTypeNode('documents'))
                                                ->append($this->buildDependentTypeNode('objects', withClasses: true))
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('objects')
                            ->fixXmlConfig('type')
                            ->fixXmlConfig('class')
                            ->canBeDisabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('types')
                                    ->info('Enable/disable cache handling for data object types.')
                                    ->beforeNormalization()
                                        ->always(fn ($v) => $v + ['folder' => false])
                                    ->end()
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
                                ->arrayNode('invalidate_dependent_elements')
                                    ->info('Enable/disable invalidation of dependent elements when an object is updated or deleted.')
                                    ->canBeEnabled()
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('types')
                                            ->info('Enable/disable invalidation of dependent element types.')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->append($this->buildDependentTypeNode('assets'))
                                                ->append($this->buildDependentTypeNode('documents'))
                                                ->append($this->buildDependentTypeNode('objects', withClasses: true))
                                            ->end()
                                        ->end()
                                    ->end()
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

    private function buildDependentTypeNode(string $name, bool $withClasses = false): ArrayNodeDefinition
    {
        $builder = new TreeBuilder($name);
        /** @var ArrayNodeDefinition $node */
        $node = $builder->getRootNode();

        $node
            ->beforeNormalization()
                ->ifTrue(fn ($v) => is_bool($v))
                ->then(fn ($v) => ['enabled' => $v])
            ->end()
            ->canBeEnabled()
            ->addDefaultsIfNotSet();

        $children = $node->children();
        $children
            ->arrayNode('types')
                ->normalizeKeys(false)
                ->useAttributeAsKey('type')
                ->defaultValue([])
                ->booleanPrototype()->end()
            ->end();

        if ($withClasses) {
            $children
                ->arrayNode('classes')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('class')
                    ->defaultValue([])
                    ->booleanPrototype()->end()
                ->end();
        }

        return $node;
    }
}
