<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Cache;

use FOS\HttpCache\Exception\ExceptionCollection;
use FOS\HttpCacheBundle\CacheManager;
use Psr\Log\LoggerInterface;

final class CacheInvalidationListener
{
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(): void
    {
        try {
            if (0 < $numberOfBans = $this->cacheManager->flush()) {
                $this->logger->info(\sprintf('Successfully flushed "%s" ban requests', $numberOfBans));
            }
        } catch (ExceptionCollection $exceptions) {
            foreach ($exceptions as $e) {
                $this->logger->error(\sprintf('Banning cache failed: %s', $e->getMessage()), ['exception' => $e]);
            }
        }
    }
}
