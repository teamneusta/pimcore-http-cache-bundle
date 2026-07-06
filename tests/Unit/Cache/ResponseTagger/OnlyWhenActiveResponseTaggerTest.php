<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\OnlyWhenActiveResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
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

    /** @var ObjectProphecy<CacheScope> */
    private ObjectProphecy $cacheScope;

    protected function setUp(): void
    {
        $this->decorated = $this->prophesize(ResponseTagger::class);
        $this->cacheScope = $this->prophesize(CacheScope::class);
        $this->subject = new OnlyWhenActiveResponseTagger(
            $this->decorated->reveal(),
            $this->cacheScope->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_invalidate_tags_when_caching_is_active(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);

        $this->cacheScope->isActive()->willReturn(true);

        $this->subject->tag($tags);

        $this->decorated->tag($tags)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_invalidate_tags_when_caching_is_not_active(): void
    {
        $tags = CacheTags::fromStrings(['tag1', 'tag2']);

        $this->cacheScope->isActive()->willReturn(false);

        $this->subject->tag($tags);

        $this->decorated->tag(Argument::any())->shouldNotHaveBeenCalled();
    }
}
