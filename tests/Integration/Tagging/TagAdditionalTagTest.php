<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\ElementCacheType;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementTaggingEvent;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementType;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[
    ConfigureRoute(__DIR__ . '/../Fixtures/get_asset_route.php'),
    ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php'),
    ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php'),
]
final class TagAdditionalTagTest extends ConfigurableWebTestcase
{
    use ArrangeCacheTest;
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
    ])]
    public function response_is_tagged_with_additional_tag_when_asset_is_loaded(): void
    {
        self::arrange(fn () => TestAssetFactory::simpleAsset(5)->save());
        self::arrange(fn () => TestAssetFactory::simpleImage(29)->save());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementTaggingEvent::class,
            fn (ElementTaggingEvent $event) => $event->addTag(
                CacheTag::fromString('29', new ElementCacheType(ElementType::Asset)),
            ),
        );

        $this->client->request('GET', '/get-asset?id=5');

        $response = $this->client->getResponse();
        self::assertSame('This is the content of the test asset.', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('a29', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_tagged_with_additional_tag_when_document_is_loaded(): void
    {
        self::arrange(fn () => TestDocumentFactory::simplePage(5)->save());
        self::arrange(fn () => TestDocumentFactory::simpleSnippet(12)->save());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementTaggingEvent::class,
            fn (ElementTaggingEvent $event) => $event->addTag(
                CacheTag::fromString('12', new ElementCacheType(ElementType::Document)),
            ),
        );

        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('d12', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function response_is_tagged_with_additional_tag_when_object_is_loaded(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject(5)->save());
        self::arrange(fn () => TestObjectFactory::simpleVariant(12)->save());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementTaggingEvent::class,
            fn (ElementTaggingEvent $event) => $event->addTag(
                CacheTag::fromString('12', new ElementCacheType(ElementType::Object)),
            ),
        );

        $this->client->request('GET', '/get-object?id=5');

        $response = $this->client->getResponse();
        self::assertSame('Test content', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('o5', $response->headers->get('X-Cache-Tags'));
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
    public function response_is_tagged_with_custom_tag_when_element_is_loaded(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject(5)->save());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementTaggingEvent::class,
            fn (ElementTaggingEvent $event) => $event->addTag(
                CacheTag::fromString('bar', new CustomCacheType('foo')),
            ),
        );

        $this->client->request('GET', '/get-object?id=5');

        $response = $this->client->getResponse();
        self::assertSame('Test content', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('foo-bar', $response->headers->get('X-Cache-Tags'));
    }
}
