<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class ElementCacheTagCheckerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $innerCacheTagChecker;

    /** @var ObjectProphecy<ElementRepository> */
    private ObjectProphecy $elementRepository;

    protected function setUp(): void
    {
        $this->innerCacheTagChecker = $this->prophesize(CacheTagChecker::class);
        $this->elementRepository = $this->prophesize(ElementRepository::class);
    }

    /**
     * @test
     */
    public function it_delegates_cache_tag_check_to_next_cache_tag_checker(): void
    {
        $tag = CacheTag::fromString('foo', new CustomCacheType('custom'));
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => true, 'types' => ['foo' => true]],
            ['enabled' => false],
            ['enabled' => false],
        );

        $this->innerCacheTagChecker->isEnabled($tag)->willReturn(true);

        self::assertTrue($elementCacheTagChecker->isEnabled($tag));
        $this->innerCacheTagChecker->isEnabled($tag)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_returns_false_when_asset_is_disabled(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => false],
        );

        $asset->getId()->willReturn(42);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_asset_is_does_not_exist(): void
    {
        $asset = $this->prophesize(Asset::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => true, 'types' => ['foo' => true]],
            ['enabled' => false],
            ['enabled' => false],
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
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => true, 'types' => ['foo' => false]],
            ['enabled' => false],
            ['enabled' => false],
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
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => true, 'types' => ['foo' => true]],
            ['enabled' => false],
            ['enabled' => false],
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
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => true, 'types' => ['foo' => false]],
            ['enabled' => false],
            ['enabled' => false],
        );

        $asset->getId()->willReturn(42);
        $asset->getType()->willReturn('bar');

        $this->elementRepository->findAsset(42)->willReturn($asset);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($asset->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_document_is_disabled(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => false],
        );

        $document->getId()->willReturn(42);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_document_is_does_not_exist(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => false],
        );

        $document->getId()->willReturn(42);
        $this->elementRepository->findDocument(42)->willReturn(null);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_document_type_is_disabled(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => true, 'types' => ['foo' => false]],
            ['enabled' => false],
        );

        $document->getId()->willReturn(42);
        $document->getType()->willReturn('foo');

        $this->elementRepository->findDocument(42)->willReturn($document);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_document_type_is_enabled(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => true, 'types' => ['foo' => true]],
            ['enabled' => false],
        );

        $document->getId()->willReturn(42);
        $document->getType()->willReturn('foo');

        $this->elementRepository->findDocument(42)->willReturn($document);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_document_type_is_not_disabled(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => true, 'types' => ['foo' => false]],
            ['enabled' => false],
        );

        $document->getId()->willReturn(42);
        $document->getType()->willReturn('bar');

        $this->elementRepository->findDocument(42)->willReturn($document);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_object_is_disabled(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => false],
        );

        $object->getId()->willReturn(42);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_object_is_does_not_exist(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => false],
        );

        $object->getId()->willReturn(42);
        $this->elementRepository->findObject(42)->willReturn(null);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_object_type_is_disabled(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => true, 'types' => ['foo' => false]],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn('foo');

        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_object_type_is_enabled(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => true, 'types' => ['foo' => true]],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn('foo');

        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_object_type_is_not_disabled(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ElementCacheTagChecker(
            $this->innerCacheTagChecker->reveal(),
            $this->elementRepository->reveal(),
            ['enabled' => false],
            ['enabled' => false],
            ['enabled' => true, 'types' => ['foo' => false]],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn('bar');

        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }
}
