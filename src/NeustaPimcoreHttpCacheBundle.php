<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use FOS\HttpCacheBundle\FOSHttpCacheBundle;
use Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass\DisableCacheTagCollectionPass;
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

    /**
     * Registers the FOSHttpCacheBundle as a dependency in the provided bundle collection.
     *
     * @param BundleCollection $collection The collection to which the dependent bundle is added.
     */
    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(FOSHttpCacheBundle::class);
    }

    /**
     * Adds the DisableCacheTagCollectionPass compiler pass to the container during the bundle build process.
     *
     * This modifies the dependency injection container compilation by registering a custom compiler pass.
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new DisableCacheTagCollectionPass());
    }
}
