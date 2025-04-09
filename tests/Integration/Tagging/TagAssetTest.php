<?php declare(strict_types=1);

namespace Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\Asset;

#[ConfigureRoute(__DIR__ . '/Fixtures/get_asset_route.yaml')]
final class TagAssetTest extends ConfigurableWebTestcase
{
    use ResetDatabase;

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
    public function response_is_tagged_with_expected_tags_when_asset_is_loaded(): void
    {
        $client = self::createClient();

        $asset = new Asset();
        $asset->setId(42);
        $asset->setFilename('test-asset.txt');
        $asset->setParentId(1);
        $asset->setData('This is the content of the test asset.');
        $asset->setMimetype('text/plain');
        $asset->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-asset?id=42');

        $response = $client->getResponse();
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
            'documents' => false,
            'objects' => true,
        ],
    ])]
    public function response_is_not_tagged_when_assets_is_not_enabled(): void
    {
        $client = self::createClient();

        $asset = new Asset();
        $asset->setId(42);
        $asset->setFilename('test-asset.txt');
        $asset->setParentId(1);
        $asset->setData('This is the content of the test asset.');
        $asset->setMimetype('text/plain');
        $asset->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-asset?id=42');

        $response = $client->getResponse();
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
            'documents' => false,
            'objects' => false,
        ],
    ])]
    public function response_is_not_tagged_when_caching_is_deactivated(): void
    {
        $client = self::createClient();

        static::getContainer()->get(CacheActivator::class)->deactivateCaching();

        $asset = new Asset();
        $asset->setId(42);
        $asset->setFilename('test-asset.txt');
        $asset->setParentId(1);
        $asset->setData('This is the content of the test asset.');
        $asset->setMimetype('text/plain');
        $asset->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-asset?id=42');

        $response = $client->getResponse();
        self::assertSame('This is the content of the test asset.', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }
}
