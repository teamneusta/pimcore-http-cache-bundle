<?php

use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use FOS\HttpCache\TagHeaderFormatter\CommaSeparatedTagHeaderFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set('fos_http_cache.http.symfony_response_tagger', SymfonyResponseTagger::class)
        ->arg('$headerFormatter', service('neusta_pimcore_http_cache.tag_header_formatter'));

    $services->set('neusta_pimcore_http_cache.tag_header_formatter', CommaSeparatedTagHeaderFormatter::class)
        ->arg('$headerName', 'xkey')
        ->arg('$glue', ' ');
};
