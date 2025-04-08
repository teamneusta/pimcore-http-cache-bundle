<?php declare(strict_types=1);

use Neusta\Pimcore\HttpCacheBundle\NeustaPimcoreHttpCacheBundle;

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    NeustaPimcoreHttpCacheBundle::class => ['all' => true],
];
