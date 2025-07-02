<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\CacheTag;

use Neusta\Pimcore\HttpCacheBundle\CacheTag;

interface CacheTagChecker
{
    public function isEnabled(CacheTag $tag): bool;
}
