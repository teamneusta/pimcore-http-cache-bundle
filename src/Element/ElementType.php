<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;

enum ElementType: string
{
    case Asset = 'asset';
    case Document = 'document';
    case Object = 'object';

    public static function fromElement(ElementInterface $element): self
    {
        return self::from(Service::getElementType($element) ?? '');
    }
}
