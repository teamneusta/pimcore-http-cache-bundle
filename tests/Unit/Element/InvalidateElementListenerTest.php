<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Dependency;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class InvalidateElementListenerTest extends TestCase
{
    use ProphecyTrait;

    private InvalidateElementListener $invalidateElementListener;

    /** @var ObjectProphecy<CacheInvalidator> */
    private $cacheInvalidator;

    /** @var ObjectProphecy<EventDispatcherInterface> */
    private $eventDispatcher;

    /** @var ObjectProphecy<ElementRepository> */
    private $elementRepository;

    protected function setUp(): void
    {
        $this->cacheInvalidator = $this->prophesize(CacheInvalidator::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->elementRepository = $this->prophesize(ElementRepository::class);
        $this->invalidateElementListener = new InvalidateElementListener(
            $this->cacheInvalidator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->elementRepository->reveal(),
        );

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturnArgument();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_does_not_dispatch_element_invalidation_event_if_save_version_only_argument_is_set(
        ElementEventInterface $event,
    ): void {
        $event->setArgument('saveVersionOnly', true);

        $this->invalidateElementListener->onUpdate($event);

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_does_not_dispatch_element_invalidation_event_if_auto_save_argument_is_set(
        ElementEventInterface $event,
    ): void {
        $event->setArgument('autoSave', true);

        $this->invalidateElementListener->onUpdate($event);

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_dispatch_element_invalidation_event(ElementEventInterface $event): void
    {
        $this->invalidateElementListener->onUpdate($event);

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_invalidate_elements(ElementEventInterface $event): void
    {
        $element = $event->getElement();

        $this->invalidateElementListener->onUpdate($event);

        $this->cacheInvalidator->invalidate(Argument::which('toString', CacheTag::fromElement($element)->toString()))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function onUpdate_should_invalidate_dependent_elements(): void
    {
        $element = $this->prophesize(DataObject\TestDataObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentElement = $this->prophesize(DataObject::class);
        $event = new DataObjectEvent($element->reveal());

        $element->getId()->willReturn(42);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentElement->getId()->willReturn(23);
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentElement->reveal());

        $this->invalidateElementListener->onUpdate($event);

        $this->cacheInvalidator->invalidate(Argument::which('toString', CacheTag::fromElement($dependentElement->reveal())->toString()))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_does_not_invalidate_when_event_was_canceled(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onUpdate($event);

        $this->cacheInvalidator->invalidate(Argument::any())
            ->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_invalidate_additional_tags_when_requested(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $additionalTag = CacheTag::fromString('tag1');
        $additionalTags = CacheTags::fromStrings(['tag2', 'tag3']);
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->addTag($additionalTag);
        $invalidationEvent->addTags($additionalTags);
        $expected = CacheTags::fromElement($element)->with($additionalTag, $additionalTags);

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onUpdate($event);

        $this->cacheInvalidator->invalidate(Argument::which('toArray', $expected->toArray()))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onDelete_should_dispatch_element_invalidation_event(ElementEventInterface $event): void
    {
        $this->invalidateElementListener->onDelete($event);

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onDelete_should_invalidate_elements(ElementEventInterface $event): void
    {
        $element = $event->getElement();

        $this->invalidateElementListener->onDelete($event);

        $this->cacheInvalidator->invalidate(Argument::which('toString', CacheTag::fromElement($element)->toString()))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function onDelete_should_invalidate_dependent_elements(): void
    {
        $element = $this->prophesize(DataObject\TestDataObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentElement = $this->prophesize(DataObject::class);
        $event = new DataObjectEvent($element->reveal());

        $element->getId()->willReturn(42);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentElement->getId()->willReturn(23);
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentElement->reveal());

        $this->invalidateElementListener->onDelete($event);

        $this->cacheInvalidator->invalidate(Argument::which('toString', CacheTag::fromElement($dependentElement->reveal())->toString()))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onDelete_does_not_invalidate_when_event_was_canceled(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onDelete($event);

        $this->cacheInvalidator->invalidate(Argument::any())
            ->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onDelete_should_invalidate_additional_tags_when_requested(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $additionalTag = CacheTag::fromString('tag1');
        $additionalTags = CacheTags::fromStrings(['tag2', 'tag3']);
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->addTag($additionalTag);
        $invalidationEvent->addTags($additionalTags);
        $expected = CacheTags::fromElement($element)->with($additionalTag, $additionalTags);

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onDelete($event);

        $this->cacheInvalidator->invalidate(Argument::which('toArray', $expected->toArray()))
            ->shouldHaveBeenCalledOnce();
    }

    public function elementProvider(): iterable
    {
        $dependency = $this->prophesize(Dependency::class);

        $asset = $this->prophesize(Asset::class);
        $asset->getId()->willReturn(42);
        $asset->getDependencies()->willReturn($dependency->reveal());
        yield 'Asset' => ['event' => new AssetEvent($asset->reveal())];

        $document = $this->prophesize(Document::class);
        $document->getId()->willReturn(42);
        $document->getDependencies()->willReturn($dependency->reveal());
        yield 'Document' => ['event' => new DocumentEvent($document->reveal())];

        $dataObject = $this->prophesize(DataObject::class);
        $dataObject->getId()->willReturn(42);
        $dataObject->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([]);
        yield 'Object' => ['event' => new DataObjectEvent($dataObject->reveal())];
    }
}
