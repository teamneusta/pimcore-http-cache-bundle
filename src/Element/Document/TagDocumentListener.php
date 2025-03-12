<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element\Document;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Pimcore\Event\Model\DocumentEvent;

final class TagDocumentListener
{
    public function __construct(
        private readonly CacheActivator $cacheActivator,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function __invoke(DocumentEvent $event): void
    {
        if (!$this->cacheActivator->isCachingActive()) {
            return;
        }

        $document = $event->getDocument();

        if (null === DocumentType::tryFrom($document->getType())) {
            return;
        }

        $this->cacheTagCollector->addTag(CacheTag::fromElement($document));
    }
}
