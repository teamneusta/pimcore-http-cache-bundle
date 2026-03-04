<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Element;

use Neusta\Pimcore\HttpCacheBundle\Element\DependentElementFinder;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementRepository;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementsConfig;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\TestObject;
use Pimcore\Model\Dependency;
use Pimcore\Model\Document;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class DependentElementFinderTest extends TestCase
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
    public function findFor_returns_empty_when_dependent_elements_are_disabled(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([]),
        );

        $element = $this->prophesize(TestObject::class);
        $element->getType()->willReturn(ElementType::Object->value);

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function findFor_skips_entries_without_id_or_type(): void
    {
        $finder = new DependentElementFinder(
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

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function findFor_skips_entries_with_unknown_type(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'unknown']]);

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function findFor_skips_entries_when_dependent_element_type_is_disabled(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => false]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
        $this->elementRepository->findObject(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_element_when_not_found(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn(null);

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_object(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentElement = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentElement->getType()->willReturn('object');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentElement->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentElement->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_returns_all_dependent_elements(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependent1 = $this->prophesize(DataObject::class);
        $dependent2 = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependent1->getType()->willReturn('object');
        $dependent2->getType()->willReturn('object');
        $dependency->getRequiredBy()->willReturn([
            ['id' => 11, 'type' => 'object'],
            ['id' => 22, 'type' => 'object'],
        ]);
        $this->elementRepository->findObject(11)->willReturn($dependent1->reveal());
        $this->elementRepository->findObject(22)->willReturn($dependent2->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(2, $result);
        self::assertSame($dependent1->reveal(), $result[0]);
        self::assertSame($dependent2->reveal(), $result[1]);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_document(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['documents' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getType()->willReturn('page');
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentDocument->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_asset(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['assets' => true]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAsset = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAsset->getType()->willReturn('image');
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAsset->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentAsset->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_element_when_source_is_an_asset(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['assets' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(Asset::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentObject = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Asset->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentObject->getType()->willReturn('object');
        $dependency->getRequiredBy()->willReturn([['id' => 9, 'type' => 'object']]);
        $this->elementRepository->findObject(9)->willReturn($dependentObject->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentObject->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_element_when_source_is_a_document(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['documents' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]]]]),
        );

        $element = $this->prophesize(Document::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentObject = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Document->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentObject->getType()->willReturn('object');
        $dependency->getRequiredBy()->willReturn([['id' => 14, 'type' => 'object']]);
        $this->elementRepository->findObject(14)->willReturn($dependentObject->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentObject->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_asset_with_explicitly_enabled_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['assets' => ['enabled' => true, 'types' => ['image' => true]]]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAsset = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAsset->getType()->willReturn('image');
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAsset->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentAsset->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_returns_dependent_document_with_explicitly_enabled_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['documents' => ['enabled' => true, 'types' => ['page' => true]]]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getType()->willReturn('page');
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertCount(1, $result);
        self::assertSame($dependentDocument->reveal(), $result[0]);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_object_with_disabled_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]], 'types' => ['folder' => false]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentFolder = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_object_with_disabled_class(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => true]], 'classes' => ['IgnoredClass' => false]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentObject = $this->prophesize(Concrete::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentObject->getType()->willReturn('object');
        $dependentObject->getClassName()->willReturn('IgnoredClass');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentObject->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_document_with_disabled_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['documents' => true]]], 'documents' => ['types' => ['email' => false]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getType()->willReturn('email');
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_object_with_subtype_disabled_in_dependent_config(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => ['enabled' => true, 'types' => ['folder' => false]]]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentFolder = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_object_with_class_disabled_in_dependent_config(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['objects' => ['enabled' => true, 'classes' => ['IgnoredClass' => false]]]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentObject = $this->prophesize(Concrete::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentObject->getType()->willReturn('object');
        $dependentObject->getClassName()->willReturn('IgnoredClass');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentObject->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * Global config allows the subtype, but dependent config disables it — element is skipped.
     */
    public function findFor_skips_dependent_object_when_global_allows_but_dependent_config_disables_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'objects' => [
                    'types' => ['folder' => true],  // global: folders allowed
                    'invalidate_dependent_elements' => ['enabled' => true, 'types' => [
                        'objects' => ['enabled' => true, 'types' => ['folder' => false]],  // dependent: folders excluded
                    ]],
                ],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentFolder = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * Dependent config allows the subtype, but global config disables it — global takes precedence.
     */
    public function findFor_skips_dependent_object_when_dependent_config_allows_but_global_disables_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'objects' => [
                    'types' => ['folder' => false],  // global: folders disabled
                    'invalidate_dependent_elements' => ['enabled' => true, 'types' => [
                        'objects' => ['enabled' => true, 'types' => ['folder' => true]],  // dependent: folders allowed
                    ]],
                ],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentFolder = $this->prophesize(DataObject::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 23, 'type' => 'object']]);
        $this->elementRepository->findObject(23)->willReturn($dependentFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_asset_with_disabled_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['assets' => true]]],
                'assets' => ['types' => ['folder' => false]],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAssetFolder = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAssetFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAssetFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_asset_with_subtype_disabled_in_dependent_config(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['assets' => ['enabled' => true, 'types' => ['folder' => false]]]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAssetFolder = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAssetFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAssetFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findFor_skips_dependent_document_with_subtype_disabled_in_dependent_config(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray(['objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => ['documents' => ['enabled' => true, 'types' => ['email' => false]]]]]]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getType()->willReturn('email');
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * Global config allows the asset subtype, but dependent config disables it — element is skipped.
     */
    public function findFor_skips_dependent_asset_when_global_allows_but_dependent_config_disables_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'assets' => ['types' => ['folder' => true]],  // global: asset folders allowed
                'objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => [
                    'assets' => ['enabled' => true, 'types' => ['folder' => false]],  // dependent: asset folders excluded
                ]]],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAssetFolder = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAssetFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAssetFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * Dependent config allows the asset subtype, but global config disables it — global takes precedence.
     */
    public function findFor_skips_dependent_asset_when_dependent_config_allows_but_global_disables_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'assets' => ['types' => ['folder' => false]],  // global: asset folders disabled
                'objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => [
                    'assets' => ['enabled' => true, 'types' => ['folder' => true]],  // dependent: asset folders allowed
                ]]],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentAssetFolder = $this->prophesize(Asset::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentAssetFolder->getType()->willReturn('folder');
        $dependency->getRequiredBy()->willReturn([['id' => 7, 'type' => 'asset']]);
        $this->elementRepository->findAsset(7)->willReturn($dependentAssetFolder->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * Global config allows the document subtype, but dependent config disables it — element is skipped.
     */
    public function findFor_skips_dependent_document_when_global_allows_but_dependent_config_disables_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'documents' => ['types' => ['link' => true]],  // global: link documents allowed
                'objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => [
                    'documents' => ['enabled' => true, 'types' => ['link' => false]],  // dependent: link documents excluded
                ]]],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getType()->willReturn('link');
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * Dependent config allows the document subtype, but global config disables it — global takes precedence.
     */
    public function findFor_skips_dependent_document_when_dependent_config_allows_but_global_disables_subtype(): void
    {
        $finder = new DependentElementFinder(
            $this->elementRepository->reveal(),
            ElementsConfig::fromArray([
                'documents' => ['types' => ['link' => false]],  // global: link documents disabled
                'objects' => ['invalidate_dependent_elements' => ['enabled' => true, 'types' => [
                    'documents' => ['enabled' => true, 'types' => ['link' => true]],  // dependent: link documents allowed
                ]]],
            ]),
        );

        $element = $this->prophesize(TestObject::class);
        $dependency = $this->prophesize(Dependency::class);
        $dependentDocument = $this->prophesize(Document::class);
        $element->getType()->willReturn(ElementType::Object->value);
        $element->getDependencies()->willReturn($dependency->reveal());
        $dependentDocument->getType()->willReturn('link');
        $dependency->getRequiredBy()->willReturn([['id' => 5, 'type' => 'document']]);
        $this->elementRepository->findDocument(5)->willReturn($dependentDocument->reveal());

        $result = $finder->findFor($element->reveal());

        self::assertSame([], $result);
    }
}
