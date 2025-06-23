<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\OnlyWhenActiveResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class OnlyWhenActiveResponseTaggerTest extends TestCase
{
    use ProphecyTrait;

    private OnlyWhenActiveResponseTagger $subject;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $decorated;

    /** @var ObjectProphecy<CacheActivator> */
    private ObjectProphecy $cacheActivator;

    protected function setUp(): void
    {
        $this->decorated = $this->prophesize(ResponseTagger::class);
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->subject = new OnlyWhenActiveResponseTagger(
            $this->decorated->reveal(),
            $this->cacheActivator->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_invalidate_tags_when_caching_is_active(): void
    {
        $tags = new CacheTags(CacheTag::fromString('tag1'), CacheTag::fromString('tag2'));

        $this->cacheActivator->isCachingActive()->willReturn(true);

        $this->subject->tag($tags);

        $this->decorated->tag($tags)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_invalidate_tags_when_caching_is_not_active(): void
    {
        $tags = new CacheTags(CacheTag::fromString('tag1'), CacheTag::fromString('tag2'));

        $this->cacheActivator->isCachingActive()->willReturn(false);

        $this->subject->tag($tags);

        $this->decorated->tag(Argument::any())->shouldNotHaveBeenCalled();
    }
}
