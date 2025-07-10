<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\ResponseTagger;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CacheTagCollectionResponseTagger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class CacheTagCollectionResponseTaggerTest extends TestCase
{
    use ProphecyTrait;

    private CacheTagCollectionResponseTagger $collectTagsResponseTagger;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $innerTagger;

    protected function setUp(): void
    {
        $this->innerTagger = $this->prophesize(ResponseTagger::class);
        $this->collectTagsResponseTagger = new CacheTagCollectionResponseTagger($this->innerTagger->reveal());
    }

    /**
     * @test
     */
    public function it_should_collect_tags(): void
    {
        $this->collectTagsResponseTagger->tag(
            new CacheTags(
                CacheTag::fromString('tag1'),
                CacheTag::fromString('tag2'),
            ));

        self::assertSame(
            'tag1,tag2',
            $this->collectTagsResponseTagger->collectedTags->toString(),
        );
    }

    /**
     * @test
     */
    public function it_should_forward_tags_to_inner_tagger(): void
    {
        $tags = new CacheTags(
            CacheTag::fromString('tag1'),
            CacheTag::fromString('tag2'),
        );

        $this->collectTagsResponseTagger->tag($tags);

        $this->innerTagger->tag($tags)->shouldHaveBeenCalledOnce();
    }
}
