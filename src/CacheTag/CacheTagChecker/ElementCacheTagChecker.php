<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagChecker;

use Neusta\Pimcore\HttpCacheBundle\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\CacheTag\CacheTagType\ElementCacheTagType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\DataObject\Concrete;

final class ElementCacheTagChecker implements CacheTagChecker
{
    /**
     * @param array{enabled: bool, types: array<string, bool>}                               $assets
     * @param array{enabled: bool, types: array<string, bool>}                               $documents
     * @param array{enabled: bool, types: array<string, bool>, classes: array<string, bool>} $objects
     */
    public function __construct(
        private readonly CacheTagChecker $inner,
        private readonly ElementRepository $repository,
        private readonly array $assets,
        private readonly array $documents,
        private readonly array $objects,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        if (!$tag->type instanceof ElementCacheTagType) {
            return $this->inner->isEnabled($tag);
        }

        return match ($tag->type->type) {
            ElementType::Asset => $this->checkAsset((int) $tag->tag),
            ElementType::Document => $this->checkDocument((int) $tag->tag),
            ElementType::Object => $this->checkObject((int) $tag->tag),
        };
    }

    private function checkAsset(int $id): bool
    {
        if (!$this->assets['enabled']) {
            return false;
        }

        if (!$asset = $this->repository->findAsset($id)) {
            return false;
        }

        return $this->assets['types'][$asset->getType()] ?? true;
    }

    private function checkDocument(int $id): bool
    {
        if (!$this->documents['enabled']) {
            return false;
        }

        if (!$document = $this->repository->findDocument($id)) {
            return false;
        }

        return $this->documents['types'][$document->getType()] ?? true;
    }

    private function checkObject(int $id): bool
    {
        if (!$this->objects['enabled']) {
            return false;
        }

        if (!$object = $this->repository->findObject($id)) {
            return false;
        }

        if (!($this->objects['types'][$object->getType()] ?? true)) {
            return false;
        }

        if (!$object instanceof Concrete) {
            return true;
        }

        return $this->objects['classes'][$object->getClassName()] ?? true;
    }
}
