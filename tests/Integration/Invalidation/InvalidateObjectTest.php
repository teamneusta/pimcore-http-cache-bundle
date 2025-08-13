<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\TestObject;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class InvalidateObjectTest extends ConfigurableKernelTestCase
{
    use ArrangeCacheTest;
    use ProphecyTrait;
    use ResetDatabase;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    private TestObject $object;

    private TestObject $variant;

    private DataObject\Folder $folder;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        $this->object = self::arrange(fn () => TestObjectFactory::simpleObject(5)->save());
        $this->folder = self::arrange(fn () => TestObjectFactory::simpleFolder(29)->save());
        $this->variant = self::arrange(fn () => TestObjectFactory::simpleVariant(70)->save());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function response_is_invalidated_when_object_is_updated(): void
    {
        $this->object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($this->object)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function dependent_object_is_invalidated_on_object_update(): void
    {
        $dependent = self::arrange(
            fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$this->object])->save(),
        );

        $this->object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($dependent)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
            'assets' => true,
        ],
    ])]
    public function dependent_object_is_invalidated_on_asset_update(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleImage(99)->save());
        $dependent = self::arrange(
            fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$asset])->save(),
        );

        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($dependent)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
            'documents' => true,
        ],
    ])]
    public function dependent_object_is_invalidated_on_document_update(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage(99)->save());
        $dependent = self::arrange(
            fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$document])->save(),
        );

        $document->setKey('updated_test_document')->save();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($dependent)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function response_is_invalidated_when_object_is_deleted(): void
    {
        $this->object->delete();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($this->object)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function dependent_object_is_invalidated_on_object_deletion(): void
    {
        $dependent = self::arrange(
            fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$this->object])->save(),
        );

        $this->object->delete();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($dependent)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
            'assets' => true,
        ],
    ])]
    public function dependent_object_is_invalidated_on_asset_deletion(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleImage(99)->save());
        $dependent = self::arrange(
            fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$asset])->save(),
        );

        $asset->delete();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($dependent)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
            'documents' => true,
        ],
    ])]
    public function dependent_object_is_invalidated_on_document_deletion(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage(99)->save());
        $dependent = self::arrange(
            fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$document])->save(),
        );

        $document->delete();

        $this->cacheManager->invalidateTags([CacheTag::fromElement($dependent)->toString()])
            ->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_object_is_of_type_folder_on_update(): void
    {
        $this->folder->setKey('updated_test_object_folder')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function response_is_not_invalidated_when_object_is_of_type_folder_on_delete(): void
    {
        $this->folder->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'types' => [
                    'variant' => true,
                ],
            ],
        ],
    ])]
    public function response_is_invalidated_when_specified_type_is_enabled_on_update(): void
    {
        $this->variant->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'types' => [
                    'variant' => false,
                ],
            ],
        ],
    ])]
    public function response_is_not_invalidated_when_specified_type_is_disabled_on_delete(): void
    {
        $this->variant->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'classes' => [
                    'TestObject' => false,
                ],
            ],
        ],
    ])]
    public function response_is_not_invalidated_when_custom_data_object_class_is_disabled_on_update(): void
    {
        $this->object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'classes' => [
                    'TestObject' => false,
                ],
            ],
        ],
    ])]
    public function response_is_not_invalidated_when_custom_data_object_class_is_disabled_on_delete(): void
    {
        $this->object->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => false,
        ],
    ])]
    public function response_is_not_invalidated_when_objects_is_disabled_on_update(): void
    {
        $this->object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => false,
        ],
    ])]
    public function response_is_not_invalidated_when_objects_is_disabled_on_delete(): void
    {
        $this->object->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }
}
