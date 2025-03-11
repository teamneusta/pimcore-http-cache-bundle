<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

final class CacheActivator
{
    private bool $isCachingActive = true;

    public function isCachingActive(): bool
    {
        return $this->isCachingActive;
    }

    public function activateCaching(): void
    {
        $this->isCachingActive = true;
    }

    public function deactivateCaching(): void
    {
        $this->isCachingActive = false;
    }
}
