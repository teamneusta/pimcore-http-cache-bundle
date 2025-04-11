<?php declare(strict_types=1);

namespace App\Service;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;

final class InvalidateAdditionalTagListener
{
    public function __invoke(ElementInvalidationEvent $event): void
    {
        $event->cacheTags->add(
            new CacheTag('additional_tag')
        );
    }
}
