<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Adapter\FOSHttpCache;

use FOS\HttpCache\ResponseTagger as FosResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\ResponseTagger;

final class ResponseTaggerAdapter implements ResponseTagger
{
    public function __construct(
        private readonly FosResponseTagger $responseTagger,
    ) {
    }

    public function tag(CacheTags $tags): void
    {
        if ($tags->isEmpty()) {
            return;
        }

        $this->responseTagger->addTags($tags->toArray());
    }
}
