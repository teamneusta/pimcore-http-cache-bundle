<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

#[
    ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php'),
    ConfigureRoute(__DIR__ . '/../Fixtures/get_asset_route.php'),
    ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php'),
]
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
            fn ($event) => $event->cacheTags->add(new CacheTag('additional_tag')),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => false,
            'objects' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_object_update(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simple()->save());

        $object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function invalidate_additional_tag_on_document_update(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
            'documents' => false,
            'objects' => false,
        ],
    ])]
    public function invalidate_additional_tag_on_asset_update(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simple()->save());

        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => false,
            'objects' => true,
        ],
    ])]
    public function invalidate_additional_tag_on_object_deletion(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simple()->save());

        $object->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
            'documents' => false,
            'objects' => false,
        ],
    ])]
    public function invalidate_additional_tag_on_asset_deletion(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simple()->save());

        $asset->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function invalidate_additional_tag_on_document_deletion(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalledTimes(1);
    }
}
