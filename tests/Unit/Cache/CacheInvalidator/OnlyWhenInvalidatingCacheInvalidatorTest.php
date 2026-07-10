<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\OnlyWhenInvalidatingCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class OnlyWhenInvalidatingCacheInvalidatorTest extends TestCase
{
    use ProphecyTrait;

    private OnlyWhenInvalidatingCacheInvalidator $subject;

    /** @var ObjectProphecy<CacheInvalidator> */
    private ObjectProphecy $decorated;

    /** @var ObjectProphecy<CacheScope> */
    private ObjectProphecy $cacheScope;

    protected function setUp(): void
    {
        $this->decorated = $this->prophesize(CacheInvalidator::class);
        $this->cacheScope = $this->prophesize(CacheScope::class);
        $this->subject = new OnlyWhenInvalidatingCacheInvalidator(
            $this->decorated->reveal(),
            $this->cacheScope->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_invalidate_tags_when_invalidation_is_active(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);

        $this->cacheScope->isInvalidating()->willReturn(true);

        $this->subject->invalidate($tags);

        $this->decorated->invalidate($tags)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_invalidate_tags_when_invalidation_is_not_active(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);

        $this->cacheScope->isInvalidating()->willReturn(false);

        $this->subject->invalidate($tags);

        $this->decorated->invalidate(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_invalidate_tags_when_collection_is_paused(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);
        $cacheScope = new CacheScope();
        $cacheScope->enable();

        $subject = new OnlyWhenInvalidatingCacheInvalidator(
            $this->decorated->reveal(),
            $cacheScope,
        );

        $cacheScope->withoutCollecting(static function () use ($subject, $tags): void {
            $subject->invalidate($tags);
        });

        $this->decorated->invalidate($tags)->shouldHaveBeenCalled();
    }
}
