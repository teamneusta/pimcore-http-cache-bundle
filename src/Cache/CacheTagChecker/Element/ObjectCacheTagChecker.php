<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementsConfig;
use Pimcore\Model\DataObject\Concrete;

final class ObjectCacheTagChecker implements CacheTagChecker
{
    public function __construct(
        private readonly ElementRepository $repository,
        private readonly ElementsConfig $config,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        \assert($tag->type instanceof ElementCacheType, \sprintf('Cache type must be an instance of %s', ElementCacheType::class));
        \assert(ElementType::Object === $tag->type->type, \sprintf('Cache type must be "%s"', ElementType::Object->value));

        if (!$this->config->isEnabled(ElementType::Object)) {
            return false;
        }

        if (!$object = $this->repository->findObject((int) $tag->tag)) {
            return false;
        }

        if (!$this->config->isTypeEnabled(ElementType::Object, $object->getType())) {
            return false;
        }

        if (!$object instanceof Concrete) {
            return true;
        }

        return $this->config->isObjectClassEnabled($object->getClassName());
    }
}
