<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\DataCollector;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeFactory;
use Neusta\Pimcore\HttpCacheBundle\Cache\DataCollector\CacheTagDataCollector;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\CacheTagCollectionResponseTagger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class CacheTagDataCollectorTest extends TestCase
{
    use ProphecyTrait;

    private CacheTagCollectionResponseTagger $collectTagsResponseTagger;

    private CacheTagDataCollector $cacheDataCollector;

    protected function setUp(): void
    {
        $tagger = $this->prophesize(ResponseTagger::class);
        $this->collectTagsResponseTagger = new CacheTagCollectionResponseTagger($tagger->reveal());
        $this->cacheDataCollector = new CacheTagDataCollector(
            $this->collectTagsResponseTagger,
        );
    }

    /**
     * @test
     */
    public function lateCollect_collect_tag_data(): void
    {
        $this->collectTagsResponseTagger->tag(new CacheTags(
            CacheTag::fromString('tag', CacheTypeFactory::createFromString('custom')),
        ));

        $this->cacheDataCollector->lateCollect();

        self::assertSame(
            [['tag' => 'custom-tag', 'type' => 'custom']],
            $this->cacheDataCollector->getTags(),
        );
    }

    /**
     * @test
     */
    public function reset_clears_collected_tags(): void
    {
        $this->collectTagsResponseTagger->tag(new CacheTags(
            CacheTag::fromString('tag', CacheTypeFactory::createFromString('custom')),
        ));

        $this->cacheDataCollector->lateCollect();
        $this->cacheDataCollector->reset();

        self::assertEmpty($this->cacheDataCollector->getTags());
    }
}
