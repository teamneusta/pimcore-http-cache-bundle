<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
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

        if ($mergedConfig['elements']['assets']['enabled']) {
            $container->getDefinition(ElementCacheTagChecker::class)
                ->setArgument('$assets', $mergedConfig['elements']['assets']);

            $container->getDefinition(TagElementListener::class)
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_LOAD]);

            $container->getDefinition(InvalidateElementListener::class)
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE, 'method' => 'onUpdated'])
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_DELETE, 'method' => 'onDeleted']);
        }

        if ($mergedConfig['elements']['documents']['enabled']) {
            $container->getDefinition(ElementCacheTagChecker::class)
                ->setArgument('$documents', $mergedConfig['elements']['documents']);

            $container->getDefinition(TagElementListener::class)
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_LOAD]);

            $container->getDefinition(InvalidateElementListener::class)
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE, 'method' => 'onUpdated'])
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_DELETE, 'method' => 'onDeleted']);
        }

        if ($mergedConfig['elements']['objects']['enabled']) {
            $container->getDefinition(ElementCacheTagChecker::class)
                ->setArgument('$objects', $mergedConfig['elements']['objects']);

            $container->getDefinition(TagElementListener::class)
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_LOAD]);

            $container->getDefinition(InvalidateElementListener::class)
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE, 'method' => 'onUpdated'])
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_DELETE, 'method' => 'onDeleted']);
        }
    }
}
