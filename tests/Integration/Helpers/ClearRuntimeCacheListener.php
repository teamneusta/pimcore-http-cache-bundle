<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers;

use Pimcore\Cache\RuntimeCache;

final class ClearRuntimeCacheListener
{
    public function __invoke(): void
    {
        // Clear the runtime cache, as it prevents the element from being loaded and thus tagged.
        RuntimeCache::clear();
    }
}
