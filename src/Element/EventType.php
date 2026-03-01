<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

enum EventType
{
    case Update;
    case Delete;
}
