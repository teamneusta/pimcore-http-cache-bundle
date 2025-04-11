<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Model\Asset;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureRoute(__DIR__ . '/../Fixtures/get_asset_route.php')]
final class InvalidateAssetTest extends ConfigurableWebTestcase
{
    use ProphecyTrait;
    use ResetDatabase;

    /** @var ObjectProphecy<KernelBrowser> */
    private $client;

    /** @var ObjectProphecy<CacheManager> */
    private $cacheManager;

    private Asset $asset;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->cacheManager = $this->prophesize(CacheManager::class);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());
        $this->asset = TestAssetFactory::simple()->save();
        parent::setUp();
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
    public function response_is_invalidated_when_asset_is_updated(): void
    {
        $this->cacheManager->invalidateTags(['a42'])
            ->willReturn($this->cacheManager->reveal());

        $this->client->request('GET', '/get-asset?id=42');

        $this->asset->setData('Updated test content')->save();

        $this->cacheManager->invalidateTags(['a42'])->shouldHaveBeenCalledTimes(2);
        $this->cacheManager->flush()->shouldHaveBeenCalledOnce();
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
    public function response_is_invalidated_when_asset_is_deleted(): void
    {
        $this->cacheManager->invalidateTags(['a42'])
            ->willReturn($this->cacheManager->reveal());

        $this->client->request('GET', '/get-asset?id=42');

        $this->asset->delete();

        $this->cacheManager->invalidateTags(['a42'])->shouldHaveBeenCalledTimes(2);
        $this->cacheManager->flush()->shouldHaveBeenCalledOnce();
    }
}
