<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Tagging;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\DataObject\TestDataObject;

#[ConfigureRoute(__DIR__ . '/Fixtures/get_object_route.php')]
final class TagObjectTest extends ConfigurableWebTestcase
{
    use ResetDatabase;

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
    public function response_is_tagged_with_expected_tags_when_object_is_loaded(): void
    {
        $client = self::createClient();

        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test_object');
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);
        $object->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-object?id=42');

        $response = $client->getResponse();
        self::assertSame('Test content', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('o42', $response->headers->get('X-Cache-Tags'));
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
    public function response_is_not_tagged_when_objects_is_not_enabled(): void
    {
        $client = self::createClient();

        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test_object');
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);
        $object->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-object?id=42');

        $response = $client->getResponse();
        self::assertSame('Test content', $response->getContent());
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
            'documents' => false,
            'objects' => true,
        ],
    ])]
    public function response_is_not_tagged_when_caching_is_deactivated(): void
    {
        $client = self::createClient();

        static::getContainer()->get(CacheActivator::class)->deactivateCaching();

        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test_object');
        $object->setContent('Test content');
        $object->setPublished(true);
        $object->setParentId(1);
        $object->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-object?id=42');

        $response = $client->getResponse();
        self::assertSame('Test content', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertNull($response->headers->get('X-Cache-Tags'));
    }
}
