<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

/**
 * @internal
 *
 * @final
 */
class ElementRepository
{
    public function findAsset(int $id): ?Asset
    {
        return Asset::getById($id);
    }

    public function findDocument(int $id): ?Document
    {
        return Document::getById($id);
    }

    public function findObject(int $id): ?DataObject
    {
        return DataObject::getById($id);
    }
}
