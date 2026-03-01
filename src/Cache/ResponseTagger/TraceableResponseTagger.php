<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Symfony\Contracts\Service\ResetInterface;

final class TraceableResponseTagger implements ResponseTagger, ResetInterface
{
    private CacheTags $recordedTags;

    public function __construct(
        private readonly ResponseTagger $inner,
    ) {
        $this->recordedTags = new CacheTags();
    }

    public function tag(CacheTags $tags): void
    {
        $this->recordedTags = $this->recordedTags->with($tags);
        $this->inner->tag($tags);
    }

    public function getRecordedTags(): CacheTags
    {
        return $this->recordedTags;
    }

    public function reset(): void
    {
        $this->recordedTags = new CacheTags();
    }
}
