<?php
declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\EventListener;

use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @internal
 */
final class ConsoleCacheScopeListener
{
    public function __construct(
        private readonly CacheScope $cacheScope,
    ) {
    }

    #[AsEventListener(event: ConsoleEvents::COMMAND)]
    public function __invoke(): void
    {
        $this->cacheScope->enable();
    }
}
