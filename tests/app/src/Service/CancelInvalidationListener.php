<?php declare(strict_types=1);

namespace App\Service;

use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;

final class CancelInvalidationListener
{
    public function __invoke(ElementInvalidationEvent $event): void
    {
        $event->cancel = true;
    }
}
