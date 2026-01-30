<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTypeFactory;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\Cache\ResponseTagger\TraceableResponseTagger;
use Neusta\Pimcore\HttpCacheBundle\DataCollector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DataCollectorTest extends TestCase
{
    use ProphecyTrait;

    private TraceableResponseTagger $traceableResponseTagger;

    private DataCollector $cacheDataCollector;

    protected function setUp(): void
    {
        $tagger = $this->prophesize(ResponseTagger::class);
        $this->traceableResponseTagger = new TraceableResponseTagger($tagger->reveal());
        $this->cacheDataCollector = new DataCollector(
            $this->traceableResponseTagger,
            ['elements' => ['objects' => false, 'assets' => false, 'documents' => true]],
        );
    }

    /**
     * @test
     */
    public function it_collects_configuration_data(): void
    {
        $this->cacheDataCollector->collect(new Request(), new Response());

        self::assertSame(
            ['elements' => ['objects' => false, 'assets' => false, 'documents' => true]],
            $this->cacheDataCollector->getConfiguration(),
        );
    }

    /**
     * @test
     */
    public function lateCollect_collects_tag_data(): void
    {
        $this->traceableResponseTagger->tag(new CacheTags(
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
        $this->traceableResponseTagger->tag(new CacheTags(
            CacheTag::fromString('tag', CacheTypeFactory::createFromString('custom')),
        ));

        $this->cacheDataCollector->lateCollect();
        $this->cacheDataCollector->reset();

        self::assertEmpty($this->cacheDataCollector->getTags());
    }
}
