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

    public static function simpleImage(): Asset\Image
    {
        $image = new Asset\Image();
        $image->setId(42);
        $image->setFilename('test-asset.jpg');
        $image->setParentId(1);
        $image->setMimetype('image/jpeg');

        return $image;
    }

    public static function simpleFolder(): Asset\Folder
    {
        $folder = new Asset\Folder();
        $folder->setKey('test-asset-folder');
        $folder->setId(23);
        $folder->setParentId(1);

        return $folder;
    }
}
