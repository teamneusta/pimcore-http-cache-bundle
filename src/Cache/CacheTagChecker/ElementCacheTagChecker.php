<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

final class ElementCacheTagChecker implements CacheTagChecker
{
    /**
     * @param array{enabled: bool, types: array<string, bool>}                               $assets
     * @param array{enabled: bool, types: array<string, bool>}                               $documents
     * @param array{enabled: bool, types: array<string, bool>, classes: array<string, bool>} $objects
     */
    public function __construct(
        private readonly CacheTagChecker $inner,
        private readonly array $assets,
        private readonly array $documents,
        private readonly array $objects,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        return match (ElementType::tryFrom($tag->type->toString())) {
            ElementType::Asset => $this->checkAsset((int) $tag->tag),
            ElementType::Document => $this->checkDocument((int) $tag->tag),
            ElementType::Object => $this->checkObject((int) $tag->tag),
            default => $this->inner->isEnabled($tag),
        };
    }

    private function checkAsset(int $id): bool
    {
        if (!$this->assets['enabled']) {
            return false;
        }

        if (!$asset = Asset::getById($id)) {
            return false;
        }

        return $this->assets['types'][$asset->getType()] ?? true;
    }

    private function checkDocument(int $id): bool
    {
        if (!$this->documents['enabled']) {
            return false;
        }

        if (!$document = Document::getById($id)) {
            return false;
        }

        return $this->documents['types'][$document->getType()] ?? true;
    }

    private function checkObject(int $id): bool
    {
        if (!$this->objects['enabled']) {
            return false;
        }

        if (!$object = DataObject::getById($id)) {
            return false;
        }

        return ($this->objects['types'][$object->getType()] ?? true)
            && ($this->objects['classes'][$object->getClassName()] ?? true);
    }
}
