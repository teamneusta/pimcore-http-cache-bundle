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
    public function it_must_be_activated_after_activateCaching_is_called(): void
    {
        $this->cacheActivator->activateCaching();

        self::assertTrue($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function it_must_be_deactivated_after_deactivateCaching_is_called(): void
    {
        $this->cacheActivator->activateCaching();
        $this->cacheActivator->deactivateCaching();

        self::assertFalse($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function it_can_be_deactivated_and_activated_again(): void
    {
        $this->cacheActivator->deactivateCaching();

        self::assertFalse($this->cacheActivator->isCachingActive());

        $this->cacheActivator->activateCaching();

        self::assertTrue($this->cacheActivator->isCachingActive());
    }
}
