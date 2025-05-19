<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

class CacheTagDataCollector extends DataCollector implements LateDataCollectorInterface
{
    public function __construct(
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // We'll collect data in lateCollect() since tags are added after the response is created
    }

    public function lateCollect(): void
    {
        $this->data = [
            'tags' => $this->cacheTagCollector->getTags(),
        ];
    }

    public function getTags(): array
    {
        return $this->data['tags'] ?? [];
    }

    public function getName(): string
    {
        return 'cache_tags';
    }
}
