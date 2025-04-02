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

        $this->registerElements($container, $mergedConfig['elements']);
    }

    /**
     * @param array<mixed> $config
     */
    private function registerElements(ContainerBuilder $container, array $config): void
    {
        $tagChecker = $container->getDefinition(ElementCacheTagChecker::class);
        $tagListener = $container->getDefinition(TagElementListener::class);
        $invalidateListener = $container->getDefinition(InvalidateElementListener::class);

        if ($config['assets']['enabled']) {
            $tagChecker->setArgument('$assets', $config['assets']);

            $tagListener->addTag('kernel.event_listener', ['event' => AssetEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE, 'method' => 'onUpdated'])
                ->addTag('kernel.event_listener', ['event' => AssetEvents::POST_DELETE, 'method' => 'onDeleted']);
        }

        if ($config['documents']['enabled']) {
            $tagChecker->setArgument('$documents', $config['documents']);

            $tagListener->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE, 'method' => 'onUpdated'])
                ->addTag('kernel.event_listener', ['event' => DocumentEvents::POST_DELETE, 'method' => 'onDeleted']);
        }

        if ($config['objects']['enabled']) {
            $tagChecker->setArgument('$objects', $config['objects']);

            $tagListener->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_LOAD]);

            $invalidateListener
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE, 'method' => 'onUpdated'])
                ->addTag('kernel.event_listener', ['event' => DataObjectEvents::POST_DELETE, 'method' => 'onDeleted']);
        }
    }
}
