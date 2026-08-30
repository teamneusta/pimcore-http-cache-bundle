<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

/**
 * @deprecated since version 0.8, use {@see CacheScope} instead.
 *
 * @phpstan-ignore class.extendsFinalByPhpDoc
 */
final class CacheActivator extends CacheScope
{
    public function isCachingActive(): bool
    {
        trigger_deprecation('teamneusta/pimcore-http-cache-bundle', '0.8', '"%s()" is deprecated, use "%s::isEnabled()" instead.', __METHOD__, CacheScope::class);

        return $this->isEnabled();
    }

    public function activateCaching(): void
    {
        trigger_deprecation('teamneusta/pimcore-http-cache-bundle', '0.8', '"%s()" is deprecated, use "%s::enable()" instead.', __METHOD__, CacheScope::class);

        $this->reset();
        $this->enable();
    }

    public function deactivateCaching(): void
    {
        trigger_deprecation('teamneusta/pimcore-http-cache-bundle', '0.8', '"%s()" is deprecated, use "%s::disable()" instead.', __METHOD__, CacheScope::class);

        $this->disable();
    }
}
