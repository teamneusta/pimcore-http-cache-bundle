<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Element\DependentElementInvalidator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementsConfig;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\TestObject;
use Pimcore\Model\Dependency;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class DependentElementInvalidatorTest extends TestCase
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
    public function invalidate_does_nothing_when_dependent_elements_are_disabled(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([]),
        );

        $element = $this->prophesize(TestObject::class);
        $element->getType()->willReturn(ElementType::Object->value);

        $called = false;
        $invalidator->invalidate($element->reveal(), function () use (&$called) { $called = true; });

        self::assertFalse($called);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidate_skips_entries_without_id_or_type(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([
            ['type' => 'object'],    // missing id
            ['id' => 23],            // missing type
            [],                      // missing both
        ]);

        $called = false;
        $invalidator->invalidate($element->reveal(), function () use (&$called) { $called = true; });

        self::assertFalse($called);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidate_skips_entries_with_unknown_type(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'unknown']]);

        $called = false;
        $invalidator->invalidate($element->reveal(), function () use (&$called) { $called = true; });

        self::assertFalse($called);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidate_skips_entries_when_dependent_element_type_is_disabled(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => false]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);

        $called = false;
        $invalidator->invalidate($element->reveal(), function () use (&$called) { $called = true; });

        self::assertFalse($called);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function invalidate_skips_dependent_element_when_not_found(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn(null);

        $called = false;
        $invalidator->invalidate($element->reveal(), function () use (&$called) { $called = true; });

        self::assertFalse($called);
    }

    /**
     * @test
     */
    public function invalidate_calls_callable_for_each_dependent_object(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentElement = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentElement->getId()->willReturn(23);
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentElement->reveal());

        $received = [];
        $invalidator->invalidate($element->reveal(), function ($e) use (&$received) { $received[] = $e; });

        self::assertCount(1, $received);
        self::assertSame($dependentElement->reveal(), $received[0]);
    }

    /**
     * @test
     */
    public function invalidate_calls_callable_for_all_dependent_elements(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependent1 = $this->prophesize(DataObject::class);
        $dependent2 = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependent1->getId()->willReturn(11);
        $dependent2->getId()->willReturn(22);
        $dependency->getRequiredBy()->willReturn([
            ['id' => 11, 'type' => 'object'],
            ['id' => 22, 'type' => 'object'],
        ]);
        $this->elementRepository->findObject(11)->willReturn($dependent1->reveal());
        $this->elementRepository->findObject(22)->willReturn($dependent2->reveal());

        $received = [];
        $invalidator->invalidate($element->reveal(), function ($e) use (&$received) { $received[] = $e; });

        self::assertCount(2, $received);
        self::assertSame($dependent1->reveal(), $received[0]);
        self::assertSame($dependent2->reveal(), $received[1]);
    }

    /**
     * @test
     */
    public function invalidate_calls_callable_for_dependent_document(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['documents' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getId()->willReturn(5);
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $received = [];
        $invalidator->invalidate($element->reveal(), function ($e) use (&$received) { $received[] = $e; });

        self::assertCount(1, $received);
        self::assertSame($dependentDocument->reveal(), $received[0]);
    }

    /**
     * @test
     */
    public function invalidate_calls_callable_for_dependent_asset(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['assets' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAsset = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAsset->getId()->willReturn(7);
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAsset->reveal());

        $received = [];
        $invalidator->invalidate($element->reveal(), function ($e) use (&$received) { $received[] = $e; });

        self::assertCount(1, $received);
        self::assertSame($dependentAsset->reveal(), $received[0]);
    }

    /**
     * @test
     */
    public function invalidate_calls_callable_when_source_is_an_asset(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['assets' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(Asset::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentObject = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Asset->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentObject->getId()->willReturn(9);
        $dependency->getRequiredBy()->willReturn([['id' => 9, 'type' => 'object']]);
        $this->elementRepository->findObject(9)->willReturn($dependentObject->reveal());

        $received = [];
        $invalidator->invalidate($element->reveal(), function ($e) use (&$received) { $received[] = $e; });

        self::assertCount(1, $received);
        self::assertSame($dependentObject->reveal(), $received[0]);
    }

    /**
     * @test
     */
    public function invalidate_calls_callable_when_source_is_a_document(): void
    {
        $invalidator = new DependentElementInvalidator(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['documents' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(Document::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentObject = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Document->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentObject->getId()->willReturn(14);
        $dependency->getRequiredBy()->willReturn([['id' => 14, 'type' => 'object']]);
        $this->elementRepository->findObject(14)->willReturn($dependentObject->reveal());

        $received = [];
        $invalidator->invalidate($element->reveal(), function ($e) use (&$received) { $received[] = $e; });

        self::assertCount(1, $received);
        self::assertSame($dependentObject->reveal(), $received[0]);
    }
}
