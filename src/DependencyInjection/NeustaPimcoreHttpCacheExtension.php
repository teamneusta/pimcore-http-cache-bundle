<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Neusta\Pimcore\HttpCacheBundle\Cache\StaticCacheTypeChecker;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class NeustaPimcoreHttpCacheExtension extends ConfigurableExtension
{
    /**
     * @param array<mixed> $mergedConfig
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));

        $loader->load('services.php');

        if ($mergedConfig['elements']['documents']) {
            $loader->load('document.php');
        }
        if ($mergedConfig['elements']['assets']) {
            $loader->load('asset.php');
        }
        if ($mergedConfig['elements']['objects']) {
            $loader->load('object.php');
        }

        $container->getDefinition(StaticCacheTypeChecker::class)->setArgument('$types', [
            ElementType::Asset->value => $mergedConfig['elements']['assets'],
            ElementType::Object->value => $mergedConfig['elements']['objects'],
            ElementType::Document->value => $mergedConfig['elements']['documents'],
        ] + $mergedConfig['cache_types']);
    }
}
