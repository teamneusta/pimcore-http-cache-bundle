<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\DataCollector;

use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CacheTagCollectionResponseTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

final class CacheTagDataCollector extends DataCollector implements LateDataCollectorInterface
{
    /**
     * Initializes the CacheTagDataCollector with a cache tag collector instance.
     *
     * @param CacheTagCollectionResponseTagger $cacheTagCollector The collector used to retrieve cache tags for HTTP responses.
     */
    public function __construct(
        private readonly CacheTagCollectionResponseTagger $cacheTagCollector,
    ) {
    }

    /**
     * No-op; data collection is deferred to lateCollect() since cache tags are added after the response is created.
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // We'll collect data in lateCollect() since tags are added after the response is created
    }

    /**
     * Collects cache tag data after the response has been generated.
     *
     * Iterates over the collected cache tags and stores each tag's string value and type identifier in the internal data array for later retrieval.
     */
    public function lateCollect(): void
    {
        foreach ($this->cacheTagCollector->collectedTags->tags as $tag) {
            $this->data['tags'][] = [
                'tag' => $tag->toString(), 'type' => $tag->type->identifier(),
            ];
        }
    }

    /**
     * Returns the collected cache tags as an array of tag and type pairs.
     *
     * @return array<array{tag: string, type: string}> An array where each element contains a 'tag' and its corresponding 'type'.
     */
    public function getTags(): array
    {
        return $this->data['tags'] ?? [];
    }

    /**
     * Returns the unique name identifier for this data collector.
     *
     * @return string The name 'cache_tags'.
     */
    public function getName(): string
    {
        return 'cache_tags';
    }

    /**
     * Clears all collected cache tag data.
     */
    public function reset(): void
    {
        $this->data['tags'] = [];
    }
}
