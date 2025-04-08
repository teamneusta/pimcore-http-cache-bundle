<?php

declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\DataObject\TestDataObject;
use Symfony\Component\HttpFoundation\Response;

final class TagObjectTest extends ConfigurableWebTestcase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function response_is_tagged_with_expected_tags(): void
    {
        self::createClient();
        self::bootKernel();


        $object = new TestDataObject();
        $object->setId(42);
        $object->setKey('test');
        $object->setPublished(true);
        $object->setParentId(1);

        $object->save();

        $client = self::getClient();

        $client->request('GET', '/get-object?id=' . '42');

        $response = $client->getResponse();

        assert($response instanceof Response);


        self::assertSame('Hello World', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['o42'], $response->headers);
    }
}
