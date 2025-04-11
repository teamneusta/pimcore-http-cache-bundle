<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use App\Service\InvalidateAdditionalTagListener;
use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementInvalidationEvent;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

final class InvalidateAdditionalTagTest extends ConfigurableWebTestcase
{
    use ProphecyTrait;
    use ResetDatabase;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->cacheManager = $this->prophesize(CacheManager::class);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());

        parent::setUp();

        $listener = new InvalidateAdditionalTagListener();
        self::getContainer()->set(InvalidateAdditionalTagListener::class, $listener);
        $dispatcher = self::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(ElementInvalidationEvent::class, $listener);
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

        $element = TestObjectFactory::simple()->save();

        $this->client->request('GET', '/get-object?id=42');

        $element->setContent('Updated test content')->save();

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

        $element = TestDocumentFactory::simplePage()->save();

        $this->client->request('GET', '/test_document_page');

        $element->setKey('updated_test_document_page')->save();

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

        $element = TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $element->setData('Updated test content')->save();

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
    public function invalidate_additional_tag_on_asset_delete(): void
    {
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $element = TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $element->delete();

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
    public function invalidate_additional_tag_on_object_delete(): void
    {
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $element = TestObjectFactory::simple()->save();

        $this->client->request('GET', '/get-object?id=42');

        $element->delete();

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
    public function invalidate_additional_tag_on_document_delete(): void
    {
        $this->cacheManager->invalidateTags(Argument::any())
            ->willReturn($this->cacheManager->reveal());

        $element = TestDocumentFactory::simplePage()->save();

        $this->client->request('GET', '/test_document_page');

        $element->delete();

        $this->cacheManager->invalidateTags(['additional_tag'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
    }
}
