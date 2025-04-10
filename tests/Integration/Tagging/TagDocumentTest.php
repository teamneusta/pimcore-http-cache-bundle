<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\Document\DocumentType;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\Document;

#[ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php')]
final class TagDocumentTest extends ConfigurableWebTestcase
{
    use ResetDatabase;

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
    public function response_is_tagged_with_expected_tags_when_page_is_loaded(): void
    {
        $client = self::createClient();

        $document = new Document\Page();
        $document->setId(42);
        $document->setKey('test_document_page');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/test_document_page');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1,d42', $response->headers->get('X-Cache-Tags'));
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
    public function response_is_tagged_with_expected_tags_when_snippet_is_loaded(): void
    {
        $client = self::createClient();

        $document = new Document\Snippet();
        $document->setId(42);
        $document->setKey('test_document_snippet');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document_snippet', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1,d42', $response->headers->get('X-Cache-Tags'));
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
    public function response_is_not_tagged_when_document_type_is_email(): void
    {
        $client = self::createClient();

        $document = new Document\Email();
        $document->setId(42);
        $document->setKey('test_document_link');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document_link', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1', $response->headers->get('X-Cache-Tags'));
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
    public function response_is_not_tagged_when_document_type_is_hard_link(): void
    {
        $client = self::createClient();

        $document = new Document\Hardlink();
        $document->setId(42);
        $document->setKey('test_document_hard_link');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document_hard_link', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1', $response->headers->get('X-Cache-Tags'));
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
    public function response_is_not_tagged_when_document_type_is_folder(): void
    {
        $client = self::createClient();

        $document = new Document\Folder();
        $document->setId(42);
        $document->setKey('test_document_folder');
        $document->setPublished(true);
        $document->setParentId(1);
        $document->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-document?id=42');

        $response = $client->getResponse();
        self::assertSame('Document with key: test_document_folder', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
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
