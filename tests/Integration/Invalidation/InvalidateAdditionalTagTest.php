<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

#[
    ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php'),
    ConfigureRoute(__DIR__ . '/../Fixtures/get_asset_route.php'),
    ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php'),
]
final class InvalidateAdditionalTagTest extends ConfigurableWebTestcase
{
    use ProphecyTrait;
    use ResetDatabase;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->cacheManager = $this->prophesize(CacheManager::class);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());
        $dispatcher = self::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(
            ElementInvalidationEvent::class,
            fn ($event) => $event->cacheTags->add(new CacheTag('additional_tag')),
        );
        parent::setUp();
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
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $object = TestObjectFactory::simple()->save();

        $this->client->request('GET', '/get-object?id=42');

        $object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
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
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $document = TestDocumentFactory::simplePage()->save();

        $this->client->request('GET', '/test_document_page');

        $document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
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
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $asset = TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
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
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $object = TestObjectFactory::simple()->save();

        $this->client->request('GET', '/get-object?id=42');

        $object->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
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
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $asset = TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $asset->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
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
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $document = TestDocumentFactory::simplePage()->save();

        $this->client->request('GET', '/test_document_page');

        $document->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
    }
}
