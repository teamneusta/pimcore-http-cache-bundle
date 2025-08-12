<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\Asset;

final class TestAssetFactory
{
    public static function simpleAsset(int $id, string $fileName = 'test-asset.txt'): Asset
    {
        $asset = new Asset();
        $asset->setId($id);
        $asset->setFilename($fileName);
        $asset->setParentId(1);
        $asset->setData('This is the content of the test asset.');
        $asset->setMimetype('text/plain');

        return $asset;
    }

    public static function simpleImage(int $id, string $fileName = 'test-asset.jpg'): Asset\Image
    {
        $image = new Asset\Image();
        $image->setId($id);
        $image->setFilename($fileName);
        $image->setParentId(1);
        $image->setMimetype('image/jpeg');

        return $image;
    }

    public static function simpleFolder(int $id, string $key = 'test-asset-folder'): Asset\Folder
    {
        $folder = new Asset\Folder();
        $folder->setId($id);
        $folder->setKey($key);
        $folder->setParentId(1);

        return $folder;
    }
}
