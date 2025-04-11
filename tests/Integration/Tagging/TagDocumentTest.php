<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Cache\RuntimeCache;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php')]
final class TagDocumentTest extends ConfigurableWebTestcase
{
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        TestDocumentFactory::simplePage()->save();
        TestDocumentFactory::simpleSnippet()->save();
        TestDocumentFactory::simpleEmail()->save();
        TestDocumentFactory::simpleHardLink()->save();
        TestDocumentFactory::simpleFolder()->save();
        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();
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
    public function response_is_tagged_with_expected_tags_when_page_is_loaded(): void
    {
        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
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
        $this->client->request('GET', '/get-document?id=23');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_snippet', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1,d23', $response->headers->get('X-Cache-Tags'));
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
        $this->client->request('GET', '/get-document?id=17');

        $response = $this->client->getResponse();
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
        $this->client->request('GET', '/get-document?id=33');

        $response = $this->client->getResponse();
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
        $this->client->request('GET', '/get-document?id=97');

        $response = $this->client->getResponse();
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
        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
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
        static::getContainer()->get(CacheActivator::class)->deactivateCaching();

        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('d1', $response->headers->get('X-Cache-Tags'));
    }
}
