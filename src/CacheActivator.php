<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

/**
 * @deprecated since version 0.7, use {@see CacheScope} instead.
 *
 * @phpstan-ignore class.extendsFinalByPhpDoc
 */
final class CacheActivator extends CacheScope
{
    public function isCachingActive(): bool
    {
        trigger_deprecation('neusta/pimcore-http-cache-bundle', '0.7', '"%s()" is deprecated, use "%s::isActive()" instead.', __METHOD__, CacheScope::class);

        return $this->isActive();
    }

    public function activateCaching(): void
    {
        trigger_deprecation('neusta/pimcore-http-cache-bundle', '0.7', '"%s()" is deprecated, use "%s::enable()" instead.', __METHOD__, CacheScope::class);

        $this->reset();
        $this->enable();
    }

    public function deactivateCaching(): void
    {
        trigger_deprecation('neusta/pimcore-http-cache-bundle', '0.7', '"%s()" is deprecated, use "%s::disable()" instead.', __METHOD__, CacheScope::class);

        $this->disable();
    }
}
