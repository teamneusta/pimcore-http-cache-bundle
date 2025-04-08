<?php

declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration;

use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\DataObject\TestDataObject;

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
    public function response_is_tagged_with_expected_tags(): void
    {
        $client = self::createClient();

        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test');
        $object->setPublished(true);
        $object->setParentId(1);
        $object->save();

        // Clear the runtime cache, as it prevents the object from being loaded and thus tagged.
        // Note: in reality, objects are created and loaded/used in separate requests.
        RuntimeCache::clear();

        $client->request('GET', '/get-object?id=42');

        $response = $client->getResponse();
        self::assertSame('Hello World', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->getCacheControlDirective('public'));
        self::assertSame('3600', $response->headers->getCacheControlDirective('s-maxage'));
        self::assertSame('o42', $response->headers->get('X-Cache-Tags'));
    }
}
