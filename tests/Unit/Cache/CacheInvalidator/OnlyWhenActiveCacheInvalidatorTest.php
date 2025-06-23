<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheInvalidator;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator\OnlyWhenActiveCacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class OnlyWhenActiveCacheInvalidatorTest extends TestCase
{
    use ProphecyTrait;

    private OnlyWhenActiveCacheInvalidator $subject;

    /** @var ObjectProphecy<CacheInvalidator> */
    private ObjectProphecy $decorated;

    /** @var ObjectProphecy<CacheActivator> */
    private ObjectProphecy $cacheActivator;

    protected function setUp(): void
    {
        $this->decorated = $this->prophesize(CacheInvalidator::class);
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->subject = new OnlyWhenActiveCacheInvalidator(
            $this->decorated->reveal(),
            $this->cacheActivator->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_invalidate_tags_when_caching_is_active(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);

        $this->cacheActivator->isCachingActive()->willReturn(true);

        $this->subject->invalidate($tags);

        $this->decorated->invalidate($tags)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_invalidate_tags_when_caching_is_not_active(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);

        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->subject->invalidate($tags);

        $this->decorated->invalidate(Argument::any())->shouldNotHaveBeenCalled();
    }
}
