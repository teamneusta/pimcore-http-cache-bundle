<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureRoute(__DIR__ . '/../Fixtures/get_asset_route.php')]
final class TagAssetTest extends ConfigurableWebTestcase
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
            'assets' => true,
        ],
    ])]
    public function response_is_tagged_with_expected_tags_when_asset_is_loaded(): void
    {
        TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $response = $this->client->getResponse();
        self::assertSame('This is the content of the test asset.', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('a42', $response->headers->get('X-Cache-Tags'));
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
        ],
    ])]
    public function response_is_not_tagged_when_assets_is_not_enabled(): void
    {
        TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $response = $this->client->getResponse();
        self::assertSame('This is the content of the test asset.', $response->getContent());
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
            'assets' => true,
        ],
    ])]
    public function response_is_not_tagged_when_caching_is_deactivated(): void
    {
        static::getContainer()->get(CacheActivator::class)->deactivateCaching();

        TestAssetFactory::simple()->save();

        $this->client->request('GET', '/get-asset?id=42');

        $response = $this->client->getResponse();
        self::assertSame('This is the content of the test asset.', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }
}
