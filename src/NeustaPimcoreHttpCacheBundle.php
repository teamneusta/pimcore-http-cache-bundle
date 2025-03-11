<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use FOS\HttpCacheBundle\FOSHttpCacheBundle;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;

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
}
