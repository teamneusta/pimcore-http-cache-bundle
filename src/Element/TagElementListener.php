<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Pimcore\Event\Model\ElementEventInterface;

final class TagElementListener
{
    public function __construct(
        private readonly CacheTagCollector $tagCollector,
    ) {
    }

    public function __invoke(ElementEventInterface $event): void
    {
        $this->tagCollector->addTag(CacheTag::fromElement($event->getElement()));
    }
}
