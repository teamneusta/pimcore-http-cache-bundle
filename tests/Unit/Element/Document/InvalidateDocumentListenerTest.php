<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element\Document;

use Neusta\Pimcore\HttpCacheBundle\Element\Document\DocumentType;
use Neusta\Pimcore\HttpCacheBundle\Element\Document\InvalidateDocumentListener;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Prophecy\PhpUnit\ProphecyTrait;

final class InvalidateDocumentListenerTest extends TestCase
{
    use ProphecyTrait;

    private InvalidateDocumentListener $invalidateDocumentListener;

    protected function setUp(): void
    {
        $this->invalidateDocumentListener = new InvalidateDocumentListener();
    }

    /**
     * @test
     *
     * @dataProvider nonDocumentProvider
     */
    public function onInvalidation_should_not_stop_propagation_for_non_document_elements(ElementInterface $element): void
    {
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);

        $this->invalidateDocumentListener->onInvalidation($invalidationEvent);

        $this->expectNotToPerformAssertions();
    }

    public function nonDocumentProvider(): iterable
    {
        yield 'Asset' => ['element' => $this->prophesize(Asset::class)->reveal()];
        yield 'DataObject' => ['element' => $this->prophesize(DataObject::class)->reveal()];
    }

    /**
     * @test
     *
     * @dataProvider documentTypeProvider
     */
    public function onInvalidation_should_not_stop_propagation_for_documents_with_existing_document_type(DocumentType $type): void
    {
        $document = $this->prophesize(Document::class);
        $invalidationEvent = ElementInvalidationEvent::fromElement($document->reveal());

        $document->getType()->willReturn($type->value);

        $this->invalidateDocumentListener->onInvalidation($invalidationEvent);

        $this->expectNotToPerformAssertions();
    }

    public function documentTypeProvider(): iterable
    {
        yield 'Page' => ['type' => DocumentType::Page];
        yield 'Link' => ['type' => DocumentType::Link];
        yield 'Snippet' => ['type' => DocumentType::Snippet];
    }

    /**
     * @test
     */
    public function onInvalidation_should_stop_propagation_for_documents_with_missing_document_type(): void
    {
        $document = $this->prophesize(Document::class);
        $invalidationEvent = ElementInvalidationEvent::fromElement($document->reveal());

        $document->getType()->willReturn('not-existing-type');

        $this->invalidateDocumentListener->onInvalidation($invalidationEvent);

        self::assertTrue($invalidationEvent->cancel);
    }
}
