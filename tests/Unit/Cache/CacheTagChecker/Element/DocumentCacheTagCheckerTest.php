<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheTagChecker\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\DocumentCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Document;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class DocumentCacheTagCheckerTest extends TestCase
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
    public function it_returns_false_when_document_is_disabled(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new DocumentCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => false, 'types' => []],
        );

        $document->getId()->willReturn(42);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_document_does_not_exist(): void
    {
        $document = $this->prophesize(Document::class);
        $elementCacheTagChecker = new DocumentCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => []],
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
        $elementCacheTagChecker = new DocumentCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => false]],
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
        $elementCacheTagChecker = new DocumentCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => true]],
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
        $elementCacheTagChecker = new DocumentCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => ['foo' => false]],
        );

        $document->getId()->willReturn(42);
        $document->getType()->willReturn('bar');
        $this->elementRepository->findDocument(42)->willReturn($document);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($document->reveal()),
        ));
    }
}
