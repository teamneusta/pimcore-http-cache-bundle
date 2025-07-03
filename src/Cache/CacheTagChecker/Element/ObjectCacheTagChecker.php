<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Pimcore\Model\DataObject\Concrete;

final class ObjectCacheTagChecker implements CacheTagChecker
{
    /**
     * @param array{enabled: bool, types: array<string, bool>, classes: array<string, bool>} $config
     */
    public function __construct(
        private readonly ElementRepository $repository,
        private readonly array $config,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        \assert($tag->type instanceof ElementCacheType, \sprintf('Cache type must be an instance of %s', ElementCacheType::class));
        \assert(ElementType::Object === $tag->type->type, \sprintf('Cache type must be "%s"', ElementType::Object->value));

        if (!$this->config['enabled']) {
            return false;
        }

        if (!$object = $this->repository->findObject((int) $tag->tag)) {
            return false;
        }

        if (!($this->config['types'][$object->getType()] ?? true)) {
            return false;
        }

        if (!$object instanceof Concrete) {
            return true;
        }

        return $this->config['classes'][$object->getClassName()] ?? true;
    }
}
