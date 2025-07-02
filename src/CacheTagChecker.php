<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

interface CacheTagChecker
{
    public function isEnabled(CacheTag $tag): bool;
}
