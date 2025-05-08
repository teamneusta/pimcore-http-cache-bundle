<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheTagChecker;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\StaticCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use PHPUnit\Framework\TestCase;

final class StaticCacheTagCheckerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_true_when_cache_type_is_empty(): void
    {
        self::assertTrue(
            (new StaticCacheTagChecker([]))->isEnabled(
                CacheTag::fromString('foo'),
            ),
        );
    }

    /**
     * @test
     */
    public function it_returns_true_when_cache_type_is_enabled(): void
    {
        self::assertTrue(
            (new StaticCacheTagChecker(['foo' => true]))->isEnabled(
                CacheTag::fromString('foo', new CustomCacheType('foo')),
            ),
        );
    }

    /**
     * @test
     */
    public function it_returns_false_when_cache_type_is_disabled(): void
    {
        self::assertFalse(
            (new StaticCacheTagChecker(['foo' => false]))->isEnabled(
                CacheTag::fromString('foo', new CustomCacheType('foo')),
            ),
        );
    }

    /**
     * @test
     */
    public function it_returns_false_when_cache_type_is_not_set(): void
    {
        self::assertFalse(
            (new StaticCacheTagChecker(['bar' => true]))->isEnabled(
                CacheTag::fromString('foo', new CustomCacheType('foo')),
            ),
        );
    }
}
