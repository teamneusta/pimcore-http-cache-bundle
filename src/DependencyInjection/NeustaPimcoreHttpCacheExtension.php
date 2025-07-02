<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\AssetCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\DocumentCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\ObjectCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\StaticCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use Neusta\Pimcore\HttpCacheBundle\Element\TagElementListener;
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

        $container->getDefinition(StaticCacheTagChecker::class)
            ->setArgument('$types', $mergedConfig['cache_types']);

        $this->registerElements($container, $mergedConfig['elements']);
    }

    /**
     * @param array<mixed> $config
     */
    private function registerElements(ContainerBuilder $container, array $config): void
    {
        $tagListener = $container->getDefinition(TagElementListener::class);
        $invalidateListener = $container->getDefinition(InvalidateElementListener::class);

        if ($config['assets']['enabled']) {
            $container->getDefinition(AssetCacheTagChecker::class)
                ->setArgument('$config', $config['assets']);

            $tagListener
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE, 'method' => 'onUpdate'])
                ->addTag('kernel.event_listener', ['event' => AssetEvents::PRE_DELETE, 'method' => 'onDelete']);
        }

        if ($config['documents']['enabled']) {
            $container->getDefinition(DocumentCacheTagChecker::class)
                ->setArgument('$config', $config['documents']);

            $tagListener
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE, 'method' => 'onUpdate'])
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::PRE_DELETE, 'method' => 'onDelete']);
        }

        if ($config['objects']['enabled']) {
            $container->getDefinition(ObjectCacheTagChecker::class)
                ->setArgument('$config', $config['objects']);

            $tagListener
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE, 'method' => 'onUpdate'])
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::PRE_DELETE, 'method' => 'onDelete']);
        }
    }
}
