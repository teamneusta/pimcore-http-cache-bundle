<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

final class InvalidateDocumentListener
{
    public function onInvalidation(ElementInvalidationEvent $event): void
    {
        if (ElementType::Document !== $event->elementType) {
            return;
        }

        if (null === DocumentType::tryFrom($event->element->getType())) {
            $event->cancel = true;
            $event->stopPropagation();
        }
    }
}
