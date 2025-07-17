<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class ConfigurationDataCollector extends DataCollector
{
    public function __construct(
        /** @var array<string, mixed> */
        private readonly array $configuration = [],
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data['configuration'] = $this->configuration;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->data['configuration'] ?? [];
    }

    public function getName(): string
    {
        return 'configuration';
    }

    public function reset(): void
    {
        $this->data['configuration'] = [];
    }
}
