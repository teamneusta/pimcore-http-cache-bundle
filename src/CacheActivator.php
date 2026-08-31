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
        trigger_deprecation('teamneusta/pimcore-http-cache-bundle', '0.8', '"%s()" is deprecated, use "%s::isInvalidating()" instead.', __METHOD__, CacheScope::class);

        return $this->isInvalidating();
    }

    /**
     * @deprecated Calls {@see CacheScope::reset()} before enabling, which also clears a prior
     * {@see CacheScope::disable()} call. Do not mix this deprecated API with the {@see CacheScope} API on
     * the same instance; the two do not compose safely.
     */
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
