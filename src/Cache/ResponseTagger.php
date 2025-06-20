<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

interface ResponseTagger
{
    public function tag(CacheTags $tags): void;
}
