<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\TestDataObject;
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

    private TestObject $otherObject;

    private TestDataObject $variant;

    private DataObject\Folder $folder;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());
        $this->object = self::arrange(fn () => TestObjectFactory::simpleObject(5)->save());
        $this->otherObject = self::arrange(fn () => TestObjectFactory::simpleObject(12, 'other_test_object', [$this->object])->save());
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

        $this->cacheManager->invalidateTags(['o5'])->shouldHaveBeenCalledTimes(1);
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
    public function dependent_element_is_invalidated_on_update(): void
    {
        $this->otherObject->setContent('Updated content')->save();

        $this->cacheManager->invalidateTags(['o5'])->shouldHaveBeenCalledTimes(1);
        $this->cacheManager->invalidateTags(['o12'])->shouldHaveBeenCalledTimes(1);
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

        $this->cacheManager->invalidateTags(['o5'])->shouldHaveBeenCalledTimes(1);
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
                    'TestDataObject' => false,
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
                    'TestDataObject' => false,
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
