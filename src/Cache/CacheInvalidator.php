<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface CacheInvalidator
{
    public function invalidate(CacheTags $tags): void;
}
