<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
class CacheScope implements ResetInterface
{
    private bool $active = false;
    private bool $disabled = false;

    /**
     * Activates the tag collection.
     *
     * Has no effect if {@see disable()] was already called.
     */
    public function enable(): void
    {
        $this->active = true;
    }

    /**
     * Permanently disables the tag collection for the current request.
     *
     * Survives {@see enable()} calls.
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    public function isActive(): bool
    {
        return $this->active && !$this->disabled;
    }

    public function reset(): void
    {
        $this->active = false;
        $this->disabled = false;
    }
}
