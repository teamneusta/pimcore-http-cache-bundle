<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CacheActivatorTest extends TestCase
{
    use ProphecyTrait;

    private CacheActivator $cacheActivator;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $responseTagger;

    protected function setUp(): void
    {
        $this->responseTagger = $this->prophesize(ResponseTagger::class);
        $this->cacheActivator = new CacheActivator(fn () => $this->responseTagger->reveal());
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

    /**
     * @test
     */
    public function without_automatic_tagging_returns_closure_result(): void
    {
        $result = $this->cacheActivator->withoutAutomaticTagging(static fn () => 'my_result');

        self::assertSame('my_result', $result);
    }

    /**
     * @test
     */
    public function without_automatic_tagging_disables_caching_during_execution(): void
    {
        $activator = $this->cacheActivator;
        $wasCachingActive = null;

        $this->cacheActivator->withoutAutomaticTagging(static function () use ($activator, &$wasCachingActive) {
            $wasCachingActive = $activator->isCachingActive();
        });

        self::assertFalse($wasCachingActive);
    }

    /**
     * @test
     */
    public function without_automatic_tagging_restores_caching_state_after_execution(): void
    {
        $this->cacheActivator->withoutAutomaticTagging(static fn () => null);

        self::assertTrue($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function without_automatic_tagging_restores_inactive_state_when_caching_was_already_disabled(): void
    {
        $this->cacheActivator->deactivateCaching();

        $this->cacheActivator->withoutAutomaticTagging(static fn () => null);

        self::assertFalse($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function without_automatic_tagging_restores_caching_state_even_when_closure_throws(): void
    {
        try {
            $this->cacheActivator->withoutAutomaticTagging(static fn () => throw new \RuntimeException('error'));
        } catch (\RuntimeException) {
        }

        self::assertTrue($this->cacheActivator->isCachingActive());
    }

    /**
     * @test
     */
    public function without_automatic_tagging_tags_a_yielded_cache_tag(): void
    {
        $tag = CacheTag::fromString('42');

        $this->cacheActivator->withoutAutomaticTagging(static function () use ($tag) {
            yield $tag;
        });

        $this->responseTagger->tag(Argument::that(
            static fn (CacheTags $tags) => $tags->toString() === $tag->toString(),
        ))->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function without_automatic_tagging_tags_a_yielded_cache_tags_collection(): void
    {
        $tags = CacheTags::fromStrings(['17', '42']);

        $this->cacheActivator->withoutAutomaticTagging(static function () use ($tags) {
            yield $tags;
        });

        $this->responseTagger->tag(Argument::that(
            static fn (CacheTags $t) => $t->toString() === $tags->toString(),
        ))->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function without_automatic_tagging_returns_generator_return_value(): void
    {
        $result = $this->cacheActivator->withoutAutomaticTagging(static function () {
            yield CacheTag::fromString('42');

            return 'generator_result';
        });

        self::assertSame('generator_result', $result);
    }

    /**
     * @test
     */
    public function without_automatic_tagging_throws_logic_exception_for_invalid_yield(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/Invalid yielded value at index 1 \(key: 0\)/');

        $this->cacheActivator->withoutAutomaticTagging(static function () {
            yield 'not_a_cache_tag';
        });
    }
}
