<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\CacheTagChecker\Element;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTagChecker\Element\ObjectCacheTagChecker;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class ObjectCacheTagCheckerTest extends TestCase
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
    public function it_returns_false_when_object_is_disabled(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => false, 'types' => [], 'classes' => []],
        );

        $object->getId()->willReturn(42);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_object_does_not_exist(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [], 'classes' => []],
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
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [AbstractObject::OBJECT_TYPE_FOLDER => false], 'classes' => []],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn(AbstractObject::OBJECT_TYPE_FOLDER);
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
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [AbstractObject::OBJECT_TYPE_VARIANT => true], 'classes' => []],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn(AbstractObject::OBJECT_TYPE_VARIANT);
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
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [AbstractObject::OBJECT_TYPE_FOLDER => false], 'classes' => []],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn(AbstractObject::OBJECT_TYPE_OBJECT);
        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_object_is_not_concrete(): void
    {
        $object = $this->prophesize(DataObject::class);
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [], 'classes' => []],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn(AbstractObject::OBJECT_TYPE_OBJECT);
        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_true_when_class_is_not_disabled(): void
    {
        $object = $this->prophesize(DataObject\Concrete::class);
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [], 'classes' => []],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn(AbstractObject::OBJECT_TYPE_OBJECT);
        $object->getClassName()->willReturn('Foo');
        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertTrue($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }

    /**
     * @test
     */
    public function it_returns_false_when_class_is_disabled(): void
    {
        $object = $this->prophesize(DataObject\Concrete::class);
        $elementCacheTagChecker = new ObjectCacheTagChecker(
            $this->elementRepository->reveal(),
            config: ['enabled' => true, 'types' => [], 'classes' => ['Foo' => false]],
        );

        $object->getId()->willReturn(42);
        $object->getType()->willReturn(AbstractObject::OBJECT_TYPE_OBJECT);
        $object->getClassName()->willReturn('Foo');
        $this->elementRepository->findObject(42)->willReturn($object);

        self::assertFalse($elementCacheTagChecker->isEnabled(
            CacheTag::fromElement($object->reveal()),
        ));
    }
}
