<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\TestDataObject;
use Pimcore\Model\DataObject\TestObject;

final class TestObjectFactory
{
    /**
     * @param list<TestObject> $related
     */
    public static function simpleObject(int $id, string $key = 'test_object', array $related = []): TestObject
    {
        $object = new TestObject();
        $object->setId($id);
        $object->setKey($key);
        $object->setContent('Test content');
        $object->setRelated($related);
        $object->setPublished(true);
        $object->setParentId(1);

        return $object;
    }

    public static function simpleVariant(int $id, string $key = 'simple_variant'): TestDataObject
    {
        $object = new TestDataObject();
        $object->setId($id);
        $object->setKey($key);
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);
        $object->setType(AbstractObject::OBJECT_TYPE_VARIANT);

        return $object;
    }

    public static function simpleFolder(int $id, string $key = 'simple_folder'): DataObject\Folder
    {
        $folder = new DataObject\Folder();
        $folder->setId($id);
        $folder->setKey($key);
        $folder->setParentId(1);

        return $folder;
    }
}
