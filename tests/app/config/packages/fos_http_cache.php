<?php declare(strict_types=1);

use Symfony\Config\FosHttpCacheConfig;

return static function (FosHttpCacheConfig $cacheConfig): void {
    $cacheConfig->proxyClient()->noop(true);

    $tags = $cacheConfig->tags();
    $tags->enabled(true);

    // Todo: remove after support for friendsofsymfony/http-cache-bundle:2 was dropped
    if (method_exists($tags, 'annotations')) {
        $tags->annotations(['enabled' => false]);
    }
};
