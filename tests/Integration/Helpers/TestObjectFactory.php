<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\TestDataObject;

final class TestObjectFactory
{
    public static function simpleObject(): TestDataObject
    {
        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test_object');
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);

        return $object;
    }

    public static function simpleVariant(): TestDataObject
    {
        $object = new TestDataObject();
        $object->setId(17);
        $object->setKey('test_variant');
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);
        $object->setType(AbstractObject::OBJECT_TYPE_VARIANT);

        return $object;
    }

    public static function simpleFolder(): DataObject\Folder
    {
        $folder = new DataObject\Folder();
        $folder->setId(23);
        $folder->setKey('test_folder');
        $folder->setParentId(1);

        return $folder;
    }
}
