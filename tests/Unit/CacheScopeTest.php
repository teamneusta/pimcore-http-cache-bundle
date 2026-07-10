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
        self::assertFalse($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function enable_enables_the_scope(): void
    {
        $cacheScope = new CacheScope();

        $cacheScope->enable();

        self::assertTrue($cacheScope->isEnabled());
        self::assertTrue($cacheScope->isCollecting());
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
        self::assertFalse($cacheScope->isCollecting());
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
        self::assertFalse($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function withoutCollecting_keeps_scope_enabled_but_stops_collection(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->withoutCollecting(static function (CacheScope $scope): void {
            self::assertTrue($scope->isEnabled());
            self::assertFalse($scope->isCollecting());
        });

        self::assertTrue($cacheScope->isEnabled());
        self::assertTrue($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function withoutCollecting_returns_the_closure_result(): void
    {
        $cacheScope = new CacheScope();

        $result = $cacheScope->withoutCollecting(static fn () => 'result');

        self::assertSame('result', $result);
    }

    /**
     * @test
     */
    public function nested_withoutCollecting_keeps_scope_disabled_until_outer_scope_finishes(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->withoutCollecting(static function (CacheScope $scope): void {
            self::assertTrue($scope->isEnabled());
            self::assertFalse($scope->isCollecting());

            $scope->withoutCollecting(static function (CacheScope $scope): void {
                self::assertTrue($scope->isEnabled());
                self::assertFalse($scope->isCollecting());
            });

            self::assertTrue($scope->isEnabled());
            self::assertFalse($scope->isCollecting());
        });

        self::assertTrue($cacheScope->isEnabled());
        self::assertTrue($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function withCollecting_temporarily_enables_the_scope(): void
    {
        $cacheScope = new CacheScope();

        self::assertFalse($cacheScope->isEnabled());
        self::assertFalse($cacheScope->isCollecting());

        $cacheScope->withCollecting(static function (CacheScope $scope): void {
            self::assertTrue($scope->isEnabled());
            self::assertTrue($scope->isCollecting());
        });

        self::assertFalse($cacheScope->isEnabled());
        self::assertFalse($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function withCollecting_returns_the_closure_result(): void
    {
        $cacheScope = new CacheScope();

        $result = $cacheScope->withCollecting(static fn () => 'result');

        self::assertSame('result', $result);
    }

    /**
     * @test
     */
    public function withCollecting_respects_disable(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->disable();

        $cacheScope->withCollecting(static function (CacheScope $scope): void {
            self::assertFalse($scope->isEnabled());
            self::assertFalse($scope->isCollecting());
        });

        self::assertFalse($cacheScope->isEnabled());
        self::assertFalse($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function withCollecting_temporarily_resumes_collection_inside_withoutCollecting(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->withoutCollecting(static function (CacheScope $scope): void {
            self::assertTrue($scope->isEnabled());
            self::assertFalse($scope->isCollecting());

            $scope->withCollecting(static function (CacheScope $scope): void {
                self::assertTrue($scope->isEnabled());
                self::assertTrue($scope->isCollecting());
            });

            self::assertTrue($scope->isEnabled());
            self::assertFalse($scope->isCollecting());
        });

        self::assertTrue($cacheScope->isEnabled());
        self::assertTrue($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function withCollecting_restores_previous_state_after_closure(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->withCollecting(static function (CacheScope $scope): void {
            self::assertTrue($scope->isEnabled());
            self::assertTrue($scope->isCollecting());
        });

        self::assertTrue($cacheScope->isEnabled());
        self::assertTrue($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function reset_disables_the_scope(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $cacheScope->reset();

        self::assertFalse($cacheScope->isEnabled());
        self::assertFalse($cacheScope->isCollecting());
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
        self::assertTrue($cacheScope->isCollecting());
    }

    /**
     * @test
     */
    public function it_is_invalidating_by_default(): void
    {
        $cacheScope = new CacheScope();

        self::assertTrue($cacheScope->isInvalidating());
    }

    /**
     * @test
     */
    public function it_is_invalidating_even_when_not_explicitly_enabled(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        self::assertTrue($cacheScope->isInvalidating());
    }

    /**
     * @test
     */
    public function disable_stops_invalidation(): void
    {
        $cacheScope = new CacheScope();

        $cacheScope->disable();

        self::assertFalse($cacheScope->isInvalidating());
    }

    /**
     * @test
     */
    public function reset_clears_disabled_state_so_invalidation_resumes(): void
    {
        $cacheScope = new CacheScope();
        $cacheScope->disable();

        $cacheScope->reset();

        self::assertTrue($cacheScope->isInvalidating());
    }
}
