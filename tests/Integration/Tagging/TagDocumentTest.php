<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheScope;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php')]
final class TagDocumentTest extends ConfigurableWebTestcase
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
            'documents' => true,
        ],
    ])]
    public function response_is_tagged_with_expected_tags_when_page_is_loaded(): void
    {
        self::arrange(static fn () => TestDocumentFactory::simplePage()->save());

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
        self::arrange(static fn () => TestDocumentFactory::simpleSnippet()->save());

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
        self::arrange(static fn () => TestDocumentFactory::simpleEmail()->save());

        $this->client->request('GET', '/get-document?id=17');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_link', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringNotContainsString('d17', $response->headers->get('X-Cache-Tags', ''));
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
        self::arrange(static fn () => TestDocumentFactory::simpleHardLink()->save());

        $this->client->request('GET', '/get-document?id=33');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_hard_link', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringNotContainsString('d33', $response->headers->get('X-Cache-Tags', ''));
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
        self::arrange(static fn () => TestDocumentFactory::simpleFolder()->save());

        $this->client->request('GET', '/get-document?id=97');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_folder', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringNotContainsString('d97', $response->headers->get('X-Cache-Tags', ''));
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
        self::arrange(static fn () => TestDocumentFactory::simplePage()->save());

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
        self::arrange(static fn () => TestDocumentFactory::simplePage()->save());
        self::getContainer()->get(CacheScope::class)->disable();

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
        'scope' => 'request',
        'elements' => [
            'documents' => true,
        ],
    ])]
    public function response_is_tagged_with_the_document_parents_when_in_request_scope(): void
    {
        $parent = self::arrange(static fn () => TestDocumentFactory::simplePage()->save());
        self::arrange(static fn () => TestDocumentFactory::simpleSnippet()->setParent($parent)->save());

        $this->client->request('GET', '/test_document_page/test_document_snippet');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_snippet', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertStringContainsString('d23', $response->headers->get('X-Cache-Tags')); // The document itself
        self::assertStringContainsString('d42', $response->headers->get('X-Cache-Tags')); // The document's parent
        self::assertStringContainsString('d1', $response->headers->get('X-Cache-Tags'));  // The document's parent's parent
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'documents' => [
                'types' => [
                    'page' => false,
                ],
            ],
        ],
    ])]
    public function response_is_not_tagged_when_type_is_disabled(): void
    {
        self::arrange(static fn () => TestDocumentFactory::simplePage()->save());

        $this->client->request('GET', '/test_document_page');

        $response = $this->client->getResponse();
        self::assertSame('Document with key: test_document_page', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }
}
