<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementTaggingEvent;
use Neusta\Pimcore\HttpCacheBundle\Element\TagElementListener;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class TagElementListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<CacheTagCollector> */
    private ObjectProphecy $cacheTagCollector;

    /** @var ObjectProphecy<EventDispatcherInterface> */
    private ObjectProphecy $eventDispatcher;

    private TagElementListener $tagElementListener;

    protected function setUp(): void
    {
        $this->cacheTagCollector = $this->prophesize(CacheTagCollector::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->tagElementListener = new TagElementListener(
            $this->cacheTagCollector->reveal(),
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
    public function it_adds_cache_tags(string $class): void
    {
        $event = $this->prophesize(ElementEventInterface::class);
        /** @var ObjectProphecy<ElementInterface> $element */
        $element = $this->prophesize($class);

        $event->getElement()->willReturn($element);
        $element->getId()->willReturn(42);

        $this->tagElementListener->__invoke($event->reveal());

        $this->cacheTagCollector->addTag(Argument::type(CacheTag::class))
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     *
     * @dataProvider elementProvider
     */
    public function it_dispatches_element_tagging_event(string $class): void
    {
        $event = $this->prophesize(ElementEventInterface::class);
        /** @var ObjectProphecy<ElementInterface> $element */
        $element = $this->prophesize($class);

        $event->getElement()->willReturn($element);
        $element->getId()->willReturn(42);

        $this->tagElementListener->__invoke($event->reveal());

        $this->eventDispatcher->dispatch(Argument::type(ElementTaggingEvent::class))
            ->shouldHaveBeenCalledTimes(1);
    }

    public function elementProvider(): iterable
    {
        yield 'Asset' => ['class' => Asset::class];
        yield 'Document' => ['class' => Document::class];
        yield 'Object' => ['class' => DataObject::class];
    }
}
