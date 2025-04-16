<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface CacheTagChecker
{
    public function isEnabled(CacheTag $tag): bool;
}
