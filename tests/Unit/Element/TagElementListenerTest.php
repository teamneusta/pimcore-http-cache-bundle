<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Element\Event\ElementTaggingEvent;
use Neusta\Pimcore\HttpCacheBundle\Element\EventListener\TagElementListener;
use Neusta\Pimcore\HttpCacheBundle\ResponseTagger;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class TagElementListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<ResponseTagger> */
    private ObjectProphecy $responseTagger;

    /** @var ObjectProphecy<EventDispatcherInterface> */
    private ObjectProphecy $eventDispatcher;

    private TagElementListener $tagElementListener;

    protected function setUp(): void
    {
        $this->responseTagger = $this->prophesize(ResponseTagger::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->tagElementListener = new TagElementListener(
            $this->responseTagger->reveal(),
            $this->eventDispatcher->reveal(),
        );

        $this->eventDispatcher->dispatch(Argument::type(ElementTaggingEvent::class))
            ->willReturnArgument();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function it_adds_cache_tags(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $expected = CacheTag::fromElement($element);

        $this->tagElementListener->__invoke($event);

        $this->responseTagger->tag(Argument::which('toString', $expected->toString()))
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function it_does_not_add_tags_when_event_was_canceled(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $taggingEvent = ElementTaggingEvent::fromElement($element);
        $taggingEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementTaggingEvent::class))
            ->willReturn($taggingEvent);

        $this->tagElementListener->__invoke($event);

        $this->responseTagger->tag(Argument::any())
            ->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function it_should_add_additional_tags_when_requested(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $additionalTag = CacheTag::fromString('tag1');
        $additionalTags = CacheTags::fromStrings(['tag2', 'tag3']);
        $taggingEvent = ElementTaggingEvent::fromElement($element);
        $taggingEvent->addTag($additionalTag);
        $taggingEvent->addTags($additionalTags);
        $expected = CacheTags::fromElement($element)->with($additionalTag, $additionalTags);

        $this->eventDispatcher->dispatch(Argument::type(ElementTaggingEvent::class))
            ->willReturn($taggingEvent);

        $this->tagElementListener->__invoke($event);

        $this->responseTagger->tag(Argument::which('toArray', $expected->toArray()))
            ->shouldHaveBeenCalledOnce();
    }

    public function elementProvider(): iterable
    {
        $asset = $this->prophesize(Asset::class);
        $asset->getId()->willReturn(42);
        yield 'Asset' => ['event' => new AssetEvent($asset->reveal())];

        $document = $this->prophesize(Document::class);
        $document->getId()->willReturn(42);
        yield 'Document' => ['event' => new DocumentEvent($document->reveal())];

        $dataObject = $this->prophesize(DataObject::class);
        $dataObject->getId()->willReturn(42);
        yield 'Object' => ['event' => new DataObjectEvent($dataObject->reveal())];
    }
}
