<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Reproduces saving an element via a non-cacheable request (e.g. a POST endpoint,
 * as used by the Pimcore admin UI), for which {@see \Neusta\Pimcore\HttpCacheBundle\EventListener\HttpCacheScopeListener}
 * never enables the {@see \Neusta\Pimcore\HttpCacheBundle\CacheScope}. Invalidation must still happen.
 */
#[ConfigureRoute(__DIR__ . '/../Fixtures/update_object_route.php')]
final class InvalidateFromNonCacheableRequestTest extends ConfigurableWebTestcase
{
    use ArrangeCacheTest;
    use ProphecyTrait;
    use ResetDatabase;

    private KernelBrowser $client;

    /** @var ObjectProphecy<CacheManager> */
    private ObjectProphecy $cacheManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->cacheManager = $this->prophesize(CacheManager::class);
        $this->cacheManager->invalidateTags(Argument::any())->willReturn($this->cacheManager->reveal());
        $this->cacheManager->flush()->willReturn(0);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    public function response_is_invalidated_when_object_is_updated_via_a_post_request(): void
    {
        self::arrange(static fn () => TestObjectFactory::simpleObject()->save());

        $this->client->request('POST', '/update-object?id=42', ['content' => 'Updated test content']);

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->cacheManager->invalidateTags(['o42'])->shouldHaveBeenCalledTimes(1);
    }
}
