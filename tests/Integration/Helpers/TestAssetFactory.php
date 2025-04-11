<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\Asset;

final class TestAssetFactory
{
    public static function simple(): Asset
    {
        $asset = new Asset();
        $asset->setId(42);
        $asset->setFilename('test-asset.txt');
        $asset->setParentId(1);
        $asset->setData('This is the content of the test asset.');
        $asset->setMimetype('text/plain');

        return $asset;
    }
}
