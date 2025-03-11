<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element\Asset;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\Asset\TagAssetListener;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Model\Asset;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TagAssetListenerTest extends TestCase
{
    use ProphecyTrait;

    private TagAssetListener $tagAssetListener;

    /** @var ObjectProphecy<CacheTagCollector> */
    private $cacheTagCollector;

    /** @var ObjectProphecy<CacheActivator> */
    private $cacheActivator;

    protected function setUp(): void
    {
        $this->cacheTagCollector = $this->prophesize(CacheTagCollector::class);
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->tagAssetListener = new TagAssetListener(
            $this->cacheActivator->reveal(),
            $this->cacheTagCollector->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_tag_elements_of_type_asset(): void
    {
        $asset = $this->prophesize(Asset::class);
        $assetEvent = new AssetEvent($asset->reveal());

        $asset->getType()->willReturn('asset');
        $asset->getId()->willReturn(42);
        $this->cacheActivator->isCachingActive()->willReturn(true);

        ($this->tagAssetListener)($assetEvent);

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_should_not_tag_assets_of_type_folder(): void
    {
        $asset = $this->prophesize(Asset::class);
        $assetEvent = new AssetEvent($asset->reveal());

        $asset->getType()->willReturn('folder');
        $asset->getId()->willReturn(42);
        $this->cacheActivator->isCachingActive()->willReturn(true);

        ($this->tagAssetListener)($assetEvent);

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_tag_assets_if_caching_is_not_active(): void
    {
        $asset = $this->prophesize(Asset::class);
        $assetEvent = new AssetEvent($asset->reveal());

        $this->cacheActivator->isCachingActive()->willReturn(false);

        ($this->tagAssetListener)($assetEvent);

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))->shouldNotHaveBeenCalled();
    }
}
