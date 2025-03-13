<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface CacheTypeChecker
{
    public function isEnabled(CacheType $type): bool;
}
