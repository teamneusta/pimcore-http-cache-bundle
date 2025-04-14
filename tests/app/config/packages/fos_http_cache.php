<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('fos_http_cache', [
        'proxy_client' => [
            'noop' => true,
        ],
        'tags' => [
            'enabled' => true,
            'annotations' => false,
        ],
    ]);
};
