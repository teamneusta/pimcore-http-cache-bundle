<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface InvalidateResponseAdapter
{
    public function invalidate(CacheTags $tags): void;
}
