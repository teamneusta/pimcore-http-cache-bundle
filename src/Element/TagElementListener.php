<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Pimcore\Event\Model\ElementEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class TagElementListener
{
    public function __construct(
        private readonly ResponseTagger $responseTagger,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(ElementEventInterface $event): void
    {
        $taggingEvent = $this->dispatcher->dispatch(ElementTaggingEvent::fromElement($event->getElement()));
        \assert($taggingEvent instanceof ElementTaggingEvent);

        if ($taggingEvent->cancel) {
            return;
        }

        $this->responseTagger->tag($taggingEvent->cacheTags());
    }
}
