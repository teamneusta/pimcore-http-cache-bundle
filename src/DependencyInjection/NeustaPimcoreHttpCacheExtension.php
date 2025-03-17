<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeChecker\ElementCacheTypeChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeChecker\StaticCacheTypeChecker;
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

        if ($mergedConfig['elements']['documents']['enabled']) {
            $loader->load('document.php');
        }

        if ($mergedConfig['elements']['assets']['enabled']) {
            $loader->load('asset.php');
        }

        if ($mergedConfig['elements']['objects']['enabled']) {
            $loader->load('object.php');
        }

        $container->getDefinition(StaticCacheTypeChecker::class)
            ->setArgument('$types', $mergedConfig['cache_types']);

        $container->getDefinition(ElementCacheTypeChecker::class)
            ->setArgument('$assets', $mergedConfig['elements']['assets'])
            ->setArgument('$documents', $mergedConfig['elements']['documents'])
            ->setArgument('$objects', $mergedConfig['elements']['objects']);
    }
}
