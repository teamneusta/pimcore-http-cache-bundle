<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

interface CacheInvalidator
{
    public function invalidate(CacheTags $tags): void;
}
