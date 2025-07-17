<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use FOS\HttpCacheBundle\FOSHttpCacheBundle;
use Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass\DisableDataCollectorPass;
use Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass\SetBundleConfigurationPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class NeustaPimcoreHttpCacheBundle extends AbstractPimcoreBundle implements DependentBundleInterface
{
    use PackageVersionTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(FOSHttpCacheBundle::class);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new DisableDataCollectorPass(), priority: -99);
        $container->addCompilerPass(new SetBundleConfigurationPass(), priority: -100);
    }
}
