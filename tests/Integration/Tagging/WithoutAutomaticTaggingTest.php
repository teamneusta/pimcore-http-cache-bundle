<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureRoute(__DIR__ . '/../Fixtures/without_automatic_tagging_route.php')]
final class WithoutAutomaticTaggingTest extends ConfigurableWebTestcase
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
    public function response_is_not_tagged_when_asset_is_loaded_without_automatic_tagging(): void
    {
        self::arrange(static fn () => TestAssetFactory::simpleAsset()->save());

        $this->client->request('GET', '/without-automatic-tagging?id=42');

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
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
    public function response_is_tagged_with_manually_yielded_tag_when_loaded_without_automatic_tagging(): void
    {
        self::arrange(static fn () => TestAssetFactory::simpleAsset()->save());

        $this->client->request('GET', '/without-automatic-tagging?id=42&yield=true');

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('a42', $response->headers->get('X-Cache-Tags'));
    }
}
