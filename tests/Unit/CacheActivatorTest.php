<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use PHPUnit\Framework\TestCase;

final class CacheActivatorTest extends TestCase
{
    private CacheActivator $cacheActivator;

    protected function setUp(): void
    {
        $this->cacheActivator = new CacheActivator();
    }

    /**
     * @test
     */
    public function it_must_be_activated_by_default(): void
    {
        self::assertTrue($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function it_must_be_deactivated_after_deactivateCaching_is_called(): void
    {
        $this->cacheActivator->deactivateCaching();

        self::assertFalse($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function it_must_be_activated_after_activateCaching_is_called(): void
    {
        $this->cacheActivator->deactivateCaching();
        $this->cacheActivator->activateCaching();

        self::assertTrue($this->cacheActivator->isCachingActive());
    }
}
