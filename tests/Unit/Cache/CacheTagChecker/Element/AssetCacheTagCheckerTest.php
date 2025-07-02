<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheTagChecker\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class AssetCacheTagCheckerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<ElementRepository> */
    private ObjectProphecy $elementRepository;

    protected function setUp(): void
    {
        $this->elementRepository = $this->prophesize(ElementRepository::class);
    }

    /**
     * @test
     */
    public function it_returns_false_when_asset_is_disabled(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new CacheTagChecker\Element\AssetCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => false, 'types' => []],
        );

        $asset->getId()->willReturn(42);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_asset_does_not_exist(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new CacheTagChecker\Element\AssetCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => true]],
        );

        $asset->getId()->willReturn(42);
        $this->elementRepository->findAsset(42)->willReturn(null);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_asset_type_is_disabled(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new CacheTagChecker\Element\AssetCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => false]],
        );

        $asset->getId()->willReturn(42);
        $asset->getType()->willReturn('foo');
        $this->elementRepository->findAsset(42)->willReturn($asset);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_asset_type_is_enabled(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new CacheTagChecker\Element\AssetCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => true]],
        );

        $asset->getId()->willReturn(42);
        $asset->getType()->willReturn('foo');
        $this->elementRepository->findAsset(42)->willReturn($asset);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_asset_type_is_not_disabled(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new CacheTagChecker\Element\AssetCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => false]],
        );

        $asset->getId()->willReturn(42);
        $asset->getType()->willReturn('bar');
        $this->elementRepository->findAsset(42)->willReturn($asset);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }
}
