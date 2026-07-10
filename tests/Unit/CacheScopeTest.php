<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit;

use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use PHPUnit\Framework\TestCase;

final class CacheScopeTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_disabled_by_default(): void
    {
        $cacheScope = new CacheScope();

        self::assertFalse($cacheScope->isEnabled());
    }

    /**
     * @test
     */
    public function enable_enables_the_scope(): void
    {
        $cacheScope = new CacheScope();

        $cacheScope->enable();

        self::assertTrue($cacheScope->isEnabled());
    }

    /**
     * @test
     */
    public function disable_disables_scope_and_collection(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->disable();

        self::assertFalse($cacheScope->isEnabled());
    }

    /**
     * @test
     */
    public function disable_survives_later_enable_calls(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->disable();

        $cacheScope->enable();

        self::assertFalse($cacheScope->isEnabled());
    }

    /**
     * @test
     */
    public function reset_deactivates_the_scope(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->reset();

        self::assertFalse($cacheScope->isEnabled());
    }

    /**
     * @test
     */
    public function reset_clears_disabled_state_so_scope_can_be_enabled_again(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->disable();

        $cacheScope->reset();
        $cacheScope->enable();

        self::assertTrue($cacheScope->isEnabled());
    }
}
