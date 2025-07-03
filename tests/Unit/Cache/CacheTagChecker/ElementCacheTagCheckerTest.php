<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheTagChecker;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\ElementCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class ElementCacheTagCheckerTest extends TestCase
{
    use ProphecyTrait;

    private ElementCacheTagChecker $elementCacheTagChecker;

    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $innerCacheTagChecker;
    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $assetCacheTagChecker;
    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $documentCacheTagChecker;
    /** @var ObjectProphecy<CacheTagChecker> */
    private ObjectProphecy $objectCacheTagChecker;

    protected function setUp(): void
    {
        $this->innerCacheTagChecker = $this->prophesize(CacheTagChecker::class);
        $this->assetCacheTagChecker = $this->prophesize(CacheTagChecker::class);
        $this->documentCacheTagChecker = $this->prophesize(CacheTagChecker::class);
        $this->objectCacheTagChecker = $this->prophesize(CacheTagChecker::class);

        $this->innerCacheTagChecker->isEnabled(Argument::any())->willReturn(false);
        $this->assetCacheTagChecker->isEnabled(Argument::any())->willReturn(false);
        $this->documentCacheTagChecker->isEnabled(Argument::any())->willReturn(false);
        $this->objectCacheTagChecker->isEnabled(Argument::any())->willReturn(false);

        $this->elementCacheTagChecker = new ElementCacheTagChecker(
            inner: $this->innerCacheTagChecker->reveal(),
            asset: $this->assetCacheTagChecker->reveal(),
            document: $this->documentCacheTagChecker->reveal(),
            object: $this->objectCacheTagChecker->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_next_checker_when_not_an_element(): void
    {
        $tag = CacheTag::fromString('foo', new CustomCacheType('custom'));

        $this->innerCacheTagChecker->isEnabled($tag)->willReturn(true);

        self::assertTrue($this->elementCacheTagChecker->isEnabled($tag));
        $this->innerCacheTagChecker->isEnabled($tag)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_delegates_to_asset_checker(): void
    {
        $asset = $this->prophesize(Asset::class);
        $asset->getId()->willReturn(42);
        $tag = CacheTag::fromElement($asset->reveal());

        $this->assetCacheTagChecker->isEnabled($tag)->willReturn(true);

        self::assertTrue($this->elementCacheTagChecker->isEnabled($tag));
        $this->assetCacheTagChecker->isEnabled($tag)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_delegates_to_document_checker(): void
    {
        $document = $this->prophesize(Document::class);
        $document->getId()->willReturn(42);
        $tag = CacheTag::fromElement($document->reveal());

        $this->documentCacheTagChecker->isEnabled($tag)->willReturn(true);

        self::assertTrue($this->elementCacheTagChecker->isEnabled($tag));
        $this->documentCacheTagChecker->isEnabled($tag)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_delegates_to_object_checker(): void
    {
        $object = $this->prophesize(DataObject::class);
        $object->getId()->willReturn(42);
        $tag = CacheTag::fromElement($object->reveal());

        $this->objectCacheTagChecker->isEnabled($tag)->willReturn(true);

        self::assertTrue($this->elementCacheTagChecker->isEnabled($tag));
        $this->objectCacheTagChecker->isEnabled($tag)->shouldHaveBeenCalledOnce();
    }
}
