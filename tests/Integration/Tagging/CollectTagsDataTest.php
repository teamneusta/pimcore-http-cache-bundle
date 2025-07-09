<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\Cache\CacheTag;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheType\CustomCacheType;
use Neusta\Pimcore\HttpCacheBundle\Cache\DataCollector\CacheTagDataCollector;
use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Element\ElementTaggingEvent;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestAssetFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;

final class CollectTagsDataTest extends ConfigurableWebTestcase
{
    use ArrangeCacheTest;
    use ResetDatabase;

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
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php')]
    public function collect_tags_for_type_document(): void
    {
        self::arrange(fn () => TestDocumentFactory::simplePage())->save();

        $this->client->request('GET', '/test_document_page');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertSame(
            [['tag' => 'd1', 'type' => 'document'], ['tag' => 'd42', 'type' => 'document']],
            $dataCollector->getTags(),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php')]
    public function collect_tags_for_type_object(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $this->client->request('GET', '/get-object?id=42');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertSame(
            [['tag' => 'o42', 'type' => 'object']],
            $dataCollector->getTags(),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => true,
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_asset_route.php')]
    public function collect_tags_of_type_asset(): void
    {
        self::arrange(fn () => TestAssetFactory::simpleAsset()->save());

        $this->client->request('GET', '/get-asset?id=42');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertSame(
            [['tag' => 'a42', 'type' => 'asset']],
            $dataCollector->getTags(),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
        'cache_types' => [
            'foo' => true,
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php')]
    public function collect_tags_of_type_custom(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        self::getContainer()->get('event_dispatcher')->addListener(
            ElementTaggingEvent::class,
            fn (ElementTaggingEvent $event) => $event->addTag(
                CacheTag::fromString('bar', new CustomCacheType('foo')),
            ),
        );

        $this->client->request('GET', '/get-object?id=42');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertContains(
            ['tag' => 'foo-bar', 'type' => 'foo'],
            $dataCollector->getTags(),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => false,
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php')]
    public function does_not_collect_tags_when_type_is_disabled(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $this->client->request('GET', '/get-object?id=42');
        $this->client->enableProfiler();
        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertEmpty($dataCollector->getTags());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => true,
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php')]
    public function does_not_collect_tags_when_caching_is_disabled(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject()->save());
        self::getContainer()->get(CacheActivator::class)->deactivateCaching();

        $this->client->request('GET', '/get-object?id=42');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertEmpty($dataCollector->getTags());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'enabled' => true,
                'types' => [
                    'variant' => false,
                ],
            ],
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php')]
    public function does_not_collect_tags_when_object_type_is_disabled(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleVariant()->save());

        $this->client->request('GET', '/get-object?id=42');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertEmpty($dataCollector->getTags());
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'objects' => [
                'classes' => [
                    'TestDataObject' => false,
                ],
                'enabled' => true,
            ],
        ],
    ])]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/get_object_route.php')]
    public function does_not_collect_tags_when_object_class_is_disabled(): void
    {
        self::arrange(fn () => TestObjectFactory::simpleObject()->save());

        $this->client->request('GET', '/get-object?id=42');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('cache_tags');
        \assert($dataCollector instanceof CacheTagDataCollector);

        self::assertEmpty($dataCollector->getTags());
    }
}
