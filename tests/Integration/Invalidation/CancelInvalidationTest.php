<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Element\Event\ElementInvalidationEvent;
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

final class CancelInvalidationTest extends ConfigurableKernelTestCase
{
    use ArrangeCacheTest;
    use ProphecyTrait;
    use ResetDatabase;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    protected function setUp(): void
    {
        $this->cacheManager = $this->prophesize(CacheManager::class);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementInvalidationEvent::class,
            fn ($event) => $event->cancel = true,
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function cancel_invalidation_on_object_update(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function cancel_invalidation_on_document_update(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
    ])]
    public function cancel_invalidation_on_asset_update(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $asset->setData('Updated test content')->save();

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
    public function cancel_invalidation_on_object_delete(): void
    {
        $object = self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $object->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function cancel_invalidation_on_document_delete(): void
    {
        $document = self::arrange(fn () => TestDocumentFactory::simplePage()->save());

        $document->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
    ])]
    public function cancel_invalidation_on_asset_delete(): void
    {
        $asset = self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $asset->delete();

        $this->cacheManager->invalidateTags(Argument::any())->shouldNotHaveBeenCalled();
    }
}
