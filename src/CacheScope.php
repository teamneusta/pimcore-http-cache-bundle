<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
class CacheScope implements ResetInterface
{
    private bool $enabled = false;
    private bool $disabled = false;

    /**
     * Enables cache-related behavior until the scope is reset.
     *
     * Has no effect if {@see disable()} was already called.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disables cache-related behavior until the scope is reset.
     *
     * Survives {@see enable()} calls.
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * Whether cache-related behavior is currently enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !$this->disabled;
    }

    /**
     * Whether cache invalidation is currently active.
     *
     * Unlike {@see isEnabled()}, invalidation is active by default and only
     * stops once {@see disable()} has been called; it does not require an
     * explicit {@see enable()} call.
     */
    public function isInvalidating(): bool
    {
        return !$this->disabled;
    }

    public function reset(): void
    {
        $this->enabled = false;
        $this->disabled = false;
    }
}
