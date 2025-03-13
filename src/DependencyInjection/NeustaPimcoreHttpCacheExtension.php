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

        if ($mergedConfig['elements']['document']) {
            $loader->load('document.php');
        }
        if ($mergedConfig['elements']['asset']) {
            $loader->load('asset.php');
        }
        if ($mergedConfig['elements']['object']) {
            $loader->load('object.php');
        }

        $container->getDefinition(StaticCacheTypeChecker::class)->setArgument('$types', [
            ElementType::Asset->value => $mergedConfig['elements']['asset'],
            ElementType::Object->value => $mergedConfig['elements']['object'],
            ElementType::Document->value => $mergedConfig['elements']['document'],
    }
}
