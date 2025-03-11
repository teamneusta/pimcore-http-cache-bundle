<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\DependencyInjection;

use Neusta\Pimcore\HttpCacheBundle\Cache\PurgeCheckerInterface;
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

        if ($mergedConfig['document']) {
            $loader->load('document.php');
        }
        if ($mergedConfig['asset']) {
            $loader->load('asset.php');
        }
        if ($mergedConfig['object']) {
            $loader->load('object.php');
        }

        $loader->load('services.php');

        $container->getDefinition(PurgeCheckerInterface::class)->setArgument('$types', [
            ElementType::Asset->value => $mergedConfig['asset'],
            ElementType::Object->value => $mergedConfig['object'],
            ElementType::Document->value => $mergedConfig['document'],
        ]);
    }
}
