<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\RemoveDisabledTagsResponseTagger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class RemoveDisabledTagsResponseTaggerTest extends TestCase
{
    use ProphecyTrait;

    private RemoveDisabledTagsResponseTagger $subject;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $decorated;

    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $cacheTagChecker;

    protected function setUp(): void
    {
        $this->decorated = $this->prophesize(ResponseTagger::class);
        $this->cacheTagChecker = $this->prophesize(CacheTagChecker::class);
        $this->subject = new RemoveDisabledTagsResponseTagger(
            $this->decorated->reveal(),
            $this->cacheTagChecker->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_remove_disabled_tags(): void
    {
        $cacheTag1 = CacheTag::fromString('tag1');
        $cacheTag2 = CacheTag::fromString('tag2');
        $tags = new CacheTags($cacheTag1, $cacheTag2);

        $this->cacheTagChecker->isEnabled($cacheTag1)->willReturn(true);
        $this->cacheTagChecker->isEnabled($cacheTag2)->willReturn(false);

        $this->subject->tag($tags);

        $this->decorated->tag(new CacheTags($cacheTag1))->shouldHaveBeenCalledOnce();
    }
}
