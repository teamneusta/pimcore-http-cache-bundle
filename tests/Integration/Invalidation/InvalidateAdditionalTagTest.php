<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
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
    use ProphecyTrait;
    use ResetDatabase;

    private CacheScope $cacheScope;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    protected function setUp(): void
    {
        $this->cacheScope = self::getContainer()->get('neusta_pimcore_http_cache.cache_scope');

        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementInvalidationEvent::class,
            static fn (ElementInvalidationEvent $event) => $event->addTag(
                CacheTag::fromString('bar', new CustomCacheType('foo')),
            ),
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
        $object = TestObjectFactory::simpleObject()->save();

        $this->cacheScope->enable();
        $object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('o42', 'foo-bar')))->shouldHaveBeenCalledTimes(1);
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
        $object = TestObjectFactory::simpleObject()->save();

        $this->cacheScope->enable();
        $object->setKey('updated_test_object')->save();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('o42', 'foo-bar')))->shouldNotHaveBeenCalled();
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
        $document = TestDocumentFactory::simplePage()->save();

        $this->cacheScope->enable();
        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('d42', 'foo-bar')))->shouldHaveBeenCalledTimes(1);
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
        $document = TestDocumentFactory::simplePage()->save();

        $this->cacheScope->enable();
        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('d42', 'foo-bar')))->shouldNotHaveBeenCalled();
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
        $asset = TestAssetFactory::simpleAsset()->save();

        $this->cacheScope->enable();
        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('a42', 'foo-bar')))->shouldHaveBeenCalledTimes(1);
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
        $asset = TestAssetFactory::simpleAsset()->save();

        $this->cacheScope->enable();
        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('a42', 'foo-bar')))->shouldNotHaveBeenCalled();
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
        $object = TestObjectFactory::simpleObject()->save();

        $this->cacheScope->enable();
        $object->delete();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('o42', 'foo-bar')))->shouldHaveBeenCalledTimes(1);
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
        $object = TestObjectFactory::simpleObject()->save();

        $this->cacheScope->enable();
        $object->delete();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('o42', 'foo-bar')))->shouldNotHaveBeenCalled();
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
        $asset = TestAssetFactory::simpleAsset()->save();

        $this->cacheScope->enable();
        $asset->delete();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('a42', 'foo-bar')))->shouldHaveBeenCalledTimes(1);
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
        $asset = TestAssetFactory::simpleAsset()->save();

        $this->cacheScope->enable();
        $asset->delete();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('a42', 'foo-bar')))->shouldNotHaveBeenCalled();
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
        $document = TestDocumentFactory::simplePage()->save();

        $this->cacheScope->enable();
        $document->delete();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('d42', 'foo-bar')))->shouldHaveBeenCalledTimes(1);
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
        $document = TestDocumentFactory::simplePage()->save();

        $this->cacheScope->enable();
        $document->delete();

        $this->cacheManager->invalidateTags(Argument::that($this->hasTags('d42', 'foo-bar')))->shouldNotHaveBeenCalled();
    }

    private function hasTags(string ...$expectedTags): callable
    {
        return static function ($tags) use ($expectedTags): bool {
            if (!\is_array($tags)) {
                return false;
            }

            foreach ($expectedTags as $expectedTag) {
                if (!\in_array($expectedTag, $tags, true)) {
                    return false;
                }
            }

            return true;
        };
    }
}
