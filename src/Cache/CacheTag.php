<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;

final class CacheTag
{
    private string $tag;

    public function __construct(string $tag)
    {
        if ('' === trim($tag)) {
            throw new \InvalidArgumentException('The cache tag must not be empty');
        }

        $this->tag = $tag;
    }

    public static function fromElement(ElementInterface $element): self
    {
        if (!$id = $element->getId()) {
            throw new \InvalidArgumentException('The given element has no id');
        }

        $type = Service::getElementType($element) ?? 'element';

        return new self($type[0] . $id);
    }

    public function toString(): string
    {
        return $this->tag;
    }
}
