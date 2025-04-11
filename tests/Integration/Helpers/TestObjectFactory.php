<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Model\DataObject\TestDataObject;

final class TestObjectFactory
{
    public static function simple(): TestDataObject
    {
        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test_object');
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);

        return $object->save();
    }
}
