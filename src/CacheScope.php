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
    private bool $paused = false;

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
     *
     * This does not imply that response tags are currently collected;
     * collection may still be paused via {@see withoutCollecting()}.
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

    /**
     * Whether response tag collection is currently enabled and not paused.
     */
    public function isCollecting(): bool
    {
        return $this->isEnabled() && !$this->paused;
    }

    /**
     * Executes the callable with tag collection temporarily paused.
     *
     * @template T
     *
     * @param (\Closure(self): T) $fn
     *
     * @return T
     */
    public function withoutCollecting(\Closure $fn): mixed
    {
        $previousPaused = $this->paused;
        $this->paused = true;

        try {
            return $fn($this);
        } finally {
            $this->paused = $previousPaused;
        }
    }

    /**
     * Executes the callable with tag collection enabled.
     *
     * Temporarily resumes collection inside {@see withoutCollecting()}, but still respects {@see disable()}.
     *
     * @template T
     *
     * @param (\Closure(self): T) $fn
     *
     * @return T
     */
    public function withCollecting(\Closure $fn): mixed
    {
        $previousEnabled = $this->enabled;
        $previousPaused = $this->paused;

        $this->enabled = true;
        $this->paused = false;

        try {
            return $fn($this);
        } finally {
            $this->enabled = $previousEnabled;
            $this->paused = $previousPaused;
        }
    }

    public function reset(): void
    {
        $this->enabled = false;
        $this->disabled = false;
        $this->paused = false;
    }
}
