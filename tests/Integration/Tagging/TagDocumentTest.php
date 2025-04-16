<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php')]
final class TagDocumentTest extends ConfigurableWebTestcase
{
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
            'documents' => true,
        ],
    ])]
    public function response_is_tagged_with_expected_tags_when_page_is_loaded(): void
    {
        TestDocumentFactory::simplePage()->save();

        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('d42', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_tagged_with_expected_tags_when_snippet_is_loaded(): void
    {
        TestDocumentFactory::simpleSnippet()->save();

        $this->client->request('GET', '/get-document?id=23');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_snippet', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('d23', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_tagged_when_document_type_is_email(): void
    {
        TestDocumentFactory::simpleEmail()->save();

        $this->client->request('GET', '/get-document?id=17');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_link', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringNotContainsString('d17', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_tagged_when_document_type_is_hard_link(): void
    {
        TestDocumentFactory::simpleHardLink()->save();

        $this->client->request('GET', '/get-document?id=33');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_hard_link', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringNotContainsString('d33', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_not_tagged_when_document_type_is_folder(): void
    {
        TestDocumentFactory::simpleFolder()->save();

        $this->client->request('GET', '/get-document?id=97');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_folder', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringNotContainsString('d97', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => false,
        ],
    ])]
    public function response_is_not_tagged_when_documents_is_not_enabled(): void
    {
        TestDocumentFactory::simplePage()->save();

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
            'documents' => true,
        ],
    ])]
    public function response_is_not_tagged_when_caching_is_deactivated(): void
    {
        static::getContainer()->get(CacheActivator::class)->deactivateCaching();

        TestDocumentFactory::simplePage()->save();

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
            'documents' => true,
        ],
    ])]
    public function request_is_tagged_with_root_document_tag_when_loaded(): void
    {
        TestDocumentFactory::simplePage()->save();

        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('d1', $response->headers->get('X-Cache-Tags'));
    }
}
