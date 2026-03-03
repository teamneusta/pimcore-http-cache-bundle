<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Element\DependencyInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
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

final class InvalidateElementListenerTest extends TestCase
{
    use ProphecyTrait;

    private InvalidateElementListener $invalidateElementListener;

    /** @var ObjectProphecy<CacheInvalidator> */
    private $cacheInvalidator;

    /** @var ObjectProphecy<EventDispatcherInterface> */
    private $eventDispatcher;

    /** @var ObjectProphecy<DependencyInvalidator> */
    private $dependencyInvalidator;

    protected function setUp(): void
    {
        $this->cacheInvalidator = $this->prophesize(CacheInvalidator::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->dependencyInvalidator = $this->prophesize(DependencyInvalidator::class);
        $this->invalidateElementListener = new InvalidateElementListener(
            $this->cacheInvalidator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dependencyInvalidator->reveal(),
        );

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturnArgument();
        $this->dependencyInvalidator->invalidate(Argument::cetera());
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
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_call_dependency_invalidator(ElementEventInterface $event): void
    {
        $element = $event->getElement();

        $this->invalidateElementListener->onUpdate($event);

        $this->dependencyInvalidator->invalidate($element, Argument::type('callable'))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_not_call_dependency_invalidator_when_source_invalidation_is_canceled(
        ElementEventInterface $event,
    ): void {
        $element = $event->getElement();
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onUpdate($event);

        $this->dependencyInvalidator->invalidate(Argument::cetera())->shouldNotHaveBeenCalled();
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
     *
     * @dataProvider elementProvider
     */
    public function onDelete_should_call_dependency_invalidator(ElementEventInterface $event): void
    {
        $element = $event->getElement();

        $this->invalidateElementListener->onDelete($event);

        $this->dependencyInvalidator->invalidate($element, Argument::type('callable'))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onDelete_should_not_call_dependency_invalidator_when_source_invalidation_is_canceled(
        ElementEventInterface $event,
    ): void {
        $element = $event->getElement();
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onDelete($event);

        $this->dependencyInvalidator->invalidate(Argument::cetera())->shouldNotHaveBeenCalled();
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
        $asset = $this->prophesize(Asset::class);
        $asset->getId()->willReturn(42);
        $asset->getType()->willReturn(ElementType::Asset->value);
        yield 'Asset' => ['event' => new AssetEvent($asset->reveal())];

        $document = $this->prophesize(Document::class);
        $document->getId()->willReturn(42);
        $document->getType()->willReturn(ElementType::Document->value);
        yield 'Document' => ['event' => new DocumentEvent($document->reveal())];

        $dataObject = $this->prophesize(DataObject::class);
        $dataObject->getId()->willReturn(42);
        $dataObject->getType()->willReturn(ElementType::Object->value);
        yield 'Object' => ['event' => new DataObjectEvent($dataObject->reveal())];
    }
}
