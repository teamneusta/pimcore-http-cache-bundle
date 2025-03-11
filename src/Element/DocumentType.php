<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

enum DocumentType: string
{
    case Page = 'page';
    case Snippet = 'snippet';
    case Link = 'link';
}
