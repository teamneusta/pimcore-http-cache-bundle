<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class NeustaPimcoreHttpCacheExtension extends ConfigurableExtension
{
    /**
     * @param array<mixed> $mergedConfig
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));

        $loader->load('services.php');

        $container->getDefinition('neusta_pimcore_http_cache.cache_tag_checker')
            ->setArgument('$types', $mergedConfig['cache_types']);

        $this->registerElements($container, $mergedConfig['elements']);
    }

    /**
     * @param array<mixed> $config
     */
    private function registerElements(ContainerBuilder $container, array $config): void
    {
        $tagListener = $container->getDefinition('neusta_pimcore_http_cache.element.tag_listener');
        $invalidateListener = $container->getDefinition('neusta_pimcore_http_cache.element.invalidate_listener');

        if ($config['assets']['enabled']) {
            $container->getDefinition('neusta_pimcore_http_cache.cache_tag_checker.element.asset')
                ->setArgument('$config', $config['assets']);

            $tagListener
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE, 'method' => 'onUpdate'])
                ->addTag('kernel.event_listener', ['event' => AssetEvents::PRE_DELETE, 'method' => 'onDelete']);
        }

        if ($config['documents']['enabled']) {
            $container->getDefinition('neusta_pimcore_http_cache.cache_tag_checker.element.document')
                ->setArgument('$config', $config['documents']);

            $tagListener
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE, 'method' => 'onUpdate'])
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::PRE_DELETE, 'method' => 'onDelete']);
        }

        if ($config['objects']['enabled']) {
            $container->getDefinition('neusta_pimcore_http_cache.cache_tag_checker.element.object')
                ->setArgument('$config', $config['objects']);

            $tagListener
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE, 'method' => 'onUpdate'])
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::PRE_DELETE, 'method' => 'onDelete']);
        }

        $container->setParameter('neusta_pimcore_http_cache.config', $config);
    }
}
