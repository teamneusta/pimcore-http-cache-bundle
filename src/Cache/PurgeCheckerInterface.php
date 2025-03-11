<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface PurgeCheckerInterface
{
    public function isEnabled(string $type): bool;

    public function disable(string $type): void;

    public function enable(string $type): void;
}
