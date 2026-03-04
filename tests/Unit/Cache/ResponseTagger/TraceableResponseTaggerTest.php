<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\TraceableResponseTagger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TraceableResponseTaggerTest extends TestCase
{
    use ProphecyTrait;

    private TraceableResponseTagger $collectTagsResponseTagger;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $innerTagger;

    protected function setUp(): void
    {
        $this->innerTagger = $this->prophesize(ResponseTagger::class);
        $this->collectTagsResponseTagger = new TraceableResponseTagger($this->innerTagger->reveal());
    }

    /**
     * @test
     */
    public function tag_should_collect_tags(): void
    {
        $this->collectTagsResponseTagger->tag(
            new CacheTags(
                CacheTag::fromString('tag1'),
                CacheTag::fromString('tag2'),
            ));

        self::assertSame(
            'tag1,tag2',
            $this->collectTagsResponseTagger->recordedTags->toString(),
        );
    }

    /**
     * @test
     */
    public function tag_should_forward_tags_to_inner_tagger(): void
    {
        $tags = new CacheTags(
            CacheTag::fromString('tag1'),
            CacheTag::fromString('tag2'),
        );

        $this->collectTagsResponseTagger->tag($tags);

        $this->innerTagger->tag($tags)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function reset_should_reset_collected_tags(): void
    {
        $this->collectTagsResponseTagger->tag(
            new CacheTags(
                CacheTag::fromString('tag1'),
                CacheTag::fromString('tag2'),
            ));

        $this->collectTagsResponseTagger->reset();

        self::assertTrue(
            $this->collectTagsResponseTagger->recordedTags->isEmpty(),
        );
    }
}
