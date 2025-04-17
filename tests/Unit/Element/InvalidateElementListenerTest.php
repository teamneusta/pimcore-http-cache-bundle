<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidatorInterface;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTags;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
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

    /** @var ObjectProphecy<CacheInvalidatorInterface> */
    private $cacheInvalidator;

    /** @var ObjectProphecy<EventDispatcherInterface> */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->cacheInvalidator = $this->prophesize(CacheInvalidatorInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->invalidateElementListener = new InvalidateElementListener(
            $this->cacheInvalidator->reveal(),
            $this->eventDispatcher->reveal(),
        );

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturnArgument();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_not_dispatch_element_invalidation_event_if_save_version_only_argument_is_set(
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
    public function onUpdate_should_not_dispatch_element_invalidation_event_if_auto_save_argument_is_set(
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

        $this->cacheInvalidator->invalidateElement($element)
            ->shouldHaveBeenCalledOnce();
        $this->cacheInvalidator->invalidateTags(Argument::type(CacheTags::class))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onUpdate_should_not_invalidate_when_event_was_canceled(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onUpdate($event);

        $this->cacheInvalidator->invalidateElement($element)
            ->shouldNotHaveBeenCalled();
        $this->cacheInvalidator->invalidateTags(Argument::type(CacheTags::class))
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
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cacheTags->add(CacheTag::fromString('tag1'));
        $invalidationEvent->cacheTags->add(CacheTag::fromString('tag2'));

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onUpdate($event);

        $this->cacheInvalidator->invalidateElement($element)
            ->shouldHaveBeenCalledOnce();
        $this->cacheInvalidator->invalidateTags($invalidationEvent->cacheTags)
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

        $this->cacheInvalidator->invalidateElement($element)
            ->shouldHaveBeenCalledOnce();
        $this->cacheInvalidator->invalidateTags(Argument::type(CacheTags::class))
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function onDelete_should_not_invalidate_when_event_was_canceled(ElementEventInterface $event): void
    {
        $element = $event->getElement();
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cancel = true;

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onDelete($event);

        $this->cacheInvalidator->invalidateElement($element)
            ->shouldNotHaveBeenCalled();
        $this->cacheInvalidator->invalidateTags(Argument::type(CacheTags::class))
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
        $invalidationEvent = ElementInvalidationEvent::fromElement($element);
        $invalidationEvent->cacheTags->add(CacheTag::fromString('tag1'));
        $invalidationEvent->cacheTags->add(CacheTag::fromString('tag2'));

        $this->eventDispatcher->dispatch(Argument::type(ElementInvalidationEvent::class))
            ->willReturn($invalidationEvent);

        $this->invalidateElementListener->onDelete($event);

        $this->cacheInvalidator->invalidateElement($element)
            ->shouldHaveBeenCalledOnce();
        $this->cacheInvalidator->invalidateTags($invalidationEvent->cacheTags)
            ->shouldHaveBeenCalledOnce();
    }

    public function elementProvider(): iterable
    {
        yield 'Asset' => ['event' => new AssetEvent($this->prophesize(Asset::class)->reveal())];
        yield 'Document' => ['event' => new DocumentEvent($this->prophesize(Document::class)->reveal())];
        yield 'DataObject' => ['event' => new DataObjectEvent($this->prophesize(DataObject::class)->reveal())];
    }
}
