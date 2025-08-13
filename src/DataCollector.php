<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle;

use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CacheTagCollectionResponseTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector as BaseDataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

final class DataCollector extends BaseDataCollector implements LateDataCollectorInterface
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        private readonly CacheTagCollectionResponseTagger $cacheTagCollector,
        array $configuration = [],
    ) {
        $this->data['configuration'] = $configuration;
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // no action needed here, as tags are collected in lateCollect
    }

    public function lateCollect(): void
    {
        foreach ($this->cacheTagCollector->collectedTags as $tag) {
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

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->data['configuration'] ?? [];
    }

    public function getName(): string
    {
        return 'pimcore_http_cache';
    }

    public function reset(): void
    {
        $this->data['tags'] = [];
    }
}
