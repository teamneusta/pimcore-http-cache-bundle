<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementsConfig;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;

final class AssetCacheTagChecker implements CacheTagChecker
{
    public function __construct(
        private readonly ElementRepository $repository,
        private readonly ElementsConfig $config,
    ) {
    }

    public function isEnabled(CacheTag $tag): bool
    {
        \assert($tag->type instanceof ElementCacheType, \sprintf('Cache type must be an instance of %s', ElementCacheType::class));
        \assert(ElementType::Asset === $tag->type->type, \sprintf('Cache type must be "%s"', ElementType::Asset->value));

        if (!$this->config->isEnabled(ElementType::Asset)) {
            return false;
        }

        if (!$asset = $this->repository->findAsset((int) $tag->tag)) {
            return false;
        }

        return $this->config->isTypeEnabled(ElementType::Asset, $asset->getType());
    }
}
