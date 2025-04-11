<?php declare(strict_types=1);

namespace App\Service;

use Pimcore\Cache\RuntimeCache;

final class ClearRuntimeListener
{
    public function __invoke(): void
    {
        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();
    }
}
