<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;

trait ArrangeCacheTest
{
    /**
     * Lets you prepare the prerequisites for your test without interfering with the caching.
     *
     * @template T
     *
     * @param \Closure():T $arrange
     *
     * @return T
     */
    public static function arrange(\Closure $arrange): mixed
    {
        $cacheActivator = self::getContainer()->get('test.cache_activator');
        \assert($cacheActivator instanceof CacheActivator);

        $wasActive = $cacheActivator->isCachingActive();
        $cacheActivator->deactivateCaching();
        try {
            return $arrange();
        } finally {
            $wasActive && $cacheActivator->activateCaching();
        }
    }
}
