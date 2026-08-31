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
    private bool $collectionPaused = false;
    private bool $invalidationPaused = false;

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
     * Whether response tag collection is currently enabled and not paused.
     */
    public function isCollecting(): bool
    {
        return $this->enabled && !$this->disabled && !$this->collectionPaused;
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
        $previousCollectionPaused = $this->collectionPaused;
        $this->collectionPaused = true;

        try {
            return $fn($this);
        } finally {
            $this->collectionPaused = $previousCollectionPaused;
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
        $previousCollectionPaused = $this->collectionPaused;

        $this->enabled = true;
        $this->collectionPaused = false;

        try {
            return $fn($this);
        } finally {
            $this->enabled = $previousEnabled;
            $this->collectionPaused = $previousCollectionPaused;
        }
    }

    /**
     * Whether cache invalidation is currently active.
     *
     * Unlike {@see isCollecting()}, invalidation is active by default and only
     * stops once {@see disable()} has been called; it does not require an
     * explicit {@see enable()} call. It may still be paused via {@see withoutInvalidating()}.
     */
    public function isInvalidating(): bool
    {
        return !$this->disabled && !$this->invalidationPaused;
    }

    /**
     * Executes the callable with cache invalidation temporarily paused.
     *
     * Useful for suppressing invalidation around code that saves elements you don't want
     * to trigger cache invalidation, e.g. bulk imports.
     *
     * @template T
     *
     * @param (\Closure(self): T) $fn
     *
     * @return T
     */
    public function withoutInvalidating(\Closure $fn): mixed
    {
        $previousInvalidationPaused = $this->invalidationPaused;
        $this->invalidationPaused = true;

        try {
            return $fn($this);
        } finally {
            $this->invalidationPaused = $previousInvalidationPaused;
        }
    }

    /**
     * Executes the callable with cache invalidation enabled.
     *
     * Temporarily resumes invalidation inside {@see withoutInvalidating()}, but still respects {@see disable()}.
     *
     * @template T
     *
     * @param (\Closure(self): T) $fn
     *
     * @return T
     */
    public function withInvalidating(\Closure $fn): mixed
    {
        $previousInvalidationPaused = $this->invalidationPaused;
        $this->invalidationPaused = false;

        try {
            return $fn($this);
        } finally {
            $this->invalidationPaused = $previousInvalidationPaused;
        }
    }

    public function reset(): void
    {
        $this->enabled = false;
        $this->disabled = false;
        $this->collectionPaused = false;
        $this->invalidationPaused = false;
    }
}
