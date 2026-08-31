<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

enum InvalidationType
{
    case Update;
    case Delete;
}
