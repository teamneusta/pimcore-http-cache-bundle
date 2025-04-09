<?php declare(strict_types=1);

namespace Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\Document\DocumentType;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\Document;

final class TagDocumentTest extends ConfigurableWebTestcase
{
    use ResetDatabase;

    /**
     * @test
     *
     * @dataProvider documentsTypeProvider
     */
    #[ConfigureRoute(__DIR__ . '/Fixtures/get_document_route.yaml')]
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function response_is_tagged_with_expected_tags_when_document_is_loaded(DocumentType $type): void
    {
        $client = self::createClient();

        $document = new Document();
        $document->setId(42);
        $document->setKey('test_document');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->setType($type->value);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1,d42', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     *
     * @dataProvider documentsTypeProvider
     */
    #[ConfigureRoute(__DIR__ . '/Fixtures/get_document_route.yaml')]
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
            'documents' => false,
            'objects' => true,
        ],
    ])]
    public function response_is_not_tagged_when_caching_documents_is_not_allowed(DocumentType $type): void
    {
        $client = self::createClient();

        $document = new Document();
        $document->setId(42);
        $document->setKey('test_document');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->setType($type->value);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }

    public function documentsTypeProvider(): iterable
    {
        yield 'page' => ['type' => DocumentType::Page];

        yield 'link' => ['type' => DocumentType::Link];

        yield 'folder' => ['type' => DocumentType::Snippet];
    }

    /**
     * @test
     *
     * @dataProvider unsupportedDocumentTypeProvider
     */
    #[ConfigureRoute(__DIR__ . '/Fixtures/get_document_route.yaml')]
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function response_is_not_tagged_when_document_type_is_not_enabled(string $type): void
    {
        $client = self::createClient();

        $document = new Document();
        $document->setId(42);
        $document->setKey('test_document');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->setType($type);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1', $response->headers->get('X-Cache-Tags'));
    }

    public function unsupportedDocumentTypeProvider(): iterable
    {
        yield 'Email' => ['type' => 'email'];

        yield 'Hardlink' => ['type' => 'hardlink'];

        yield 'Folder' => ['type' => 'folder'];
    }

    /**
     * @test
     */
    #[ConfigureRoute(__DIR__ . '/Fixtures/get_document_route.yaml')]
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => false,
            'objects' => false,
        ],
    ])]
    public function response_is_not_tagged_when_documents_is_not_enabled(): void
    {
        $client = self::createClient();

        $document = new Document();
        $document->setId(42);
        $document->setKey('test_document');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->setType(DocumentType::Page->value);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureRoute(__DIR__ . '/Fixtures/get_document_route.yaml')]
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function response_is_not_tagged_when_caching_is_deactivated(): void
    {
        $client = self::createClient();

        static::getContainer()->get(CacheActivator::class)->deactivateCaching();

        $document = new Document();
        $document->setId(42);
        $document->setKey('test_document');
        $document->setPublished(false);
        $document->setParentId(1);
        $document->setType(DocumentType::Page->value);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }
}
