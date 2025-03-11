<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element\Object;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\Object\TagObjectListener;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class TagObjectListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<CacheTagCollector> */
    private $cacheTagCollector;

    /** @var ObjectProphecy<CacheActivator> */
    private $cacheActivator;

    private TagObjectListener $tagObjectListener;

    protected function setUp(): void
    {
        $this->cacheTagCollector = $this->prophesize(CacheTagCollector::class);
        $this->cacheActivator = $this->prophesize(CacheActivator::class);
        $this->tagObjectListener = new TagObjectListener(
            $this->cacheTagCollector->reveal(),
            $this->cacheActivator->reveal(),
        );
    }

    /**
     * @test
     */
    public function it_should_tag_elements_of_type_object(): void
    {
        $object = $this->prophesize(DataObject::class);
        $objectEvent = new DataObjectEvent($object->reveal());
        $cacheTag = new CacheTag('o42');

        $this->cacheActivator->isCachingActive()->willReturn(true);
        $object->getType()->willReturn('object');
        $object->getId()->willReturn(42);

        ($this->tagObjectListener)($objectEvent);

        $this->cacheTagCollector->addTag($cacheTag)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_should_not_tag_objects_of_type_folder(): void
    {
        $object = $this->prophesize(DataObject::class);
        $objectEvent = new DataObjectEvent($object->reveal());

        $this->cacheActivator->isCachingActive()->willReturn(true);
        $object->getType()->willReturn('folder');

        ($this->tagObjectListener)($objectEvent);

        $this->cacheTagCollector->addTag(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_not_tag_objects_if_caching_is_not_active(): void
    {
        $object = $this->prophesize(DataObject::class);
        $objectEvent = new DataObjectEvent($object->reveal());

        $this->cacheActivator->isCachingActive()->willReturn(false);

        ($this->tagObjectListener)($objectEvent);

        $this->cacheTagCollector->addTag(Argument::any())->shouldNotHaveBeenCalled();
    }
}
