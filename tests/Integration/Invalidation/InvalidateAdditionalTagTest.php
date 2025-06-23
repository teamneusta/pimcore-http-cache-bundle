<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class InvalidateAdditionalTagTest extends ConfigurableKernelTestCase
{
    use ArrangeCacheTest;
    use ProphecyTrait;
    use ResetDatabase;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementInvalidationEvent::class,
            fn ($event) => $event->cacheTags->add(CacheTag::fromString('bar', new CustomCacheType('foo'))),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_object_update(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(['o42', 'foo-bar'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
        'cache_types' => [
            'foo' => false,
        ],
    ])]
    public function does_not_invalidate_additional_tag_on_object_update_when_cache_type_is_disabled(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $object->setKey('updated_test_object')->save();

        $this->cacheManager->invalidateTags(['o42', 'foo-bar'])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_document_update(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(['d42', 'foo-bar'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
        'cache_types' => [
            'foo' => false,
        ],
    ])]
    public function does_not_invalidate_additional_tag_on_document_update_when_cache_type_is_disabled(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(['d42', 'foo-bar'])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_asset_update(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(['a42', 'foo-bar'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
        'cache_types' => [
            'foo' => false,
        ],
    ])]
    public function does_not_invalidate_additional_tag_on_asset_update_when_cache_type_is_disabled(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(['a42', 'foo-bar'])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_object_deletion(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $object->delete();

        $this->cacheManager->invalidateTags(['o42', 'foo-bar'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
        'cache_types' => [
            'foo' => false,
        ],
    ])]
    public function does_not_invalidate_additional_tag_on_object_deletion_when_cache_type_is_disabled(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $object->delete();

        $this->cacheManager->invalidateTags(['o42', 'foo-bar'])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_asset_deletion(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $asset->delete();

        $this->cacheManager->invalidateTags(['a42', 'foo-bar'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
        'cache_types' => [
            'foo' => false,
        ],
    ])]
    public function does_not_invalidate_additional_tag_on_asset_deletion_when_cache_type_is_disabled(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $asset->delete();

        $this->cacheManager->invalidateTags(['a42', 'foo-bar'])->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_document_deletion(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->delete();

        $this->cacheManager->invalidateTags(['d42', 'foo-bar'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
        'cache_types' => [
            'foo' => false,
        ],
    ])]
    public function does_not_invalidate_additional_tag_on_document_deletion_when_cache_type_was_disabled(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->delete();

        $this->cacheManager->invalidateTags(['d42', 'foo-bar'])->shouldNotHaveBeenCalled();
    }
}
