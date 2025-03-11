<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache;

use FOS\HttpCache\Exception\ExceptionCollection;
use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidationListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

final class CacheInvalidationListenerTest extends TestCase
{
    use ProphecyTrait;

    private CacheInvalidationListener $cacheInvalidationListener;

    /** @var ObjectProphecy<CacheManager> */
    private $cacheManager;

    /** @var ObjectProphecy<LoggerInterface> */
    private $logger;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->cacheInvalidationListener = new CacheInvalidationListener(
            $this->cacheManager->reveal(),
            $this->logger->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_flush_cache(): void
    {
        $this->cacheManager->flush()->willReturn(1);

        ($this->cacheInvalidationListener)();

        $this->cacheManager->flush()->shouldHaveBeenCalledOnce();
        $this->logger->info('Successfully flushed "1" ban requests')->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_should_log_error_when_cache_flush_fails(): void
    {
        $exception1 = new \Exception('Cache flush failed for tag 001');
        $exception2 = new \Exception('Cache flush failed for tag 002');
        $exception = new ExceptionCollection([$exception1, $exception2]);

        $this->cacheManager->flush()->willThrow($exception);

        ($this->cacheInvalidationListener)();

        $this->logger->error('Banning cache failed: Cache flush failed for tag 001', ['exception' => $exception1])->shouldHaveBeenCalledOnce();
        $this->logger->error('Banning cache failed: Cache flush failed for tag 002', ['exception' => $exception2])->shouldHaveBeenCalledOnce();
    }
}
