<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;

enum ElementType: string
{
    case ASSET = 'asset';
    case DOCUMENT = 'document';
    case OBJECT = 'object';

    public static function fromElement(ElementInterface $element): self
    {
        return self::from(Service::getElementType($element) ?? '');
    }
}
