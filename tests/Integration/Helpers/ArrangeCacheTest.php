<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Neusta\Pimcore\HttpCacheBundle\CacheScope;

trait ArrangeCacheTest
{
    /**
     * Lets you prepare the prerequisites for your test without triggering tagging or invalidation.
     *
     * @template T
     *
     * @param \Closure():T $arrange
     *
     * @return T
     */
    public static function arrange(\Closure $arrange): mixed
    {
        $cacheScope = self::getContainer()->get('neusta_pimcore_http_cache.cache_scope');
        \assert($cacheScope instanceof CacheScope);

        $cacheScope->disable();
        try {
            return $arrange();
        } finally {
            $cacheScope->reset();
        }
    }
}
