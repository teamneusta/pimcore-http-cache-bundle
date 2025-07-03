<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CollectTagsResponseTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

final class CacheTagDataCollector extends DataCollector implements LateDataCollectorInterface
{
    public function __construct(
        private readonly CollectTagsResponseTagger $cacheTagCollector,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // We'll collect data in lateCollect() since tags are added after the response is created
    }

    public function lateCollect(): void
    {
        foreach ($this->cacheTagCollector->collectedTags->unique()->tags as $tag) {
            $this->data['tags'][] = [
                'tag' => $tag->toString(), 'type' => $tag->type->identifier(),
            ];
        }
    }

    /**
     * @return array<array{tag: string, type: string}>
     */
    public function getTags(): array
    {
        return $this->data['tags'] ?? [];
    }

    public function getName(): string
    {
        return 'cache_tags';
    }

    public function reset(): void
    {
        $this->data['tags'] = [];
    }
}
