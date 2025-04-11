<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestObjectFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Model\DataObject\TestDataObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class InvalidateObjectTest extends ConfigurableWebTestcase
{
    use ProphecyTrait;
    use ResetDatabase;

    private KernelBrowser $client;

    /** @var ObjectProphecy<CacheManager> */
    private $cacheManager;

    private TestDataObject $object;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->cacheManager = $this->prophesize(CacheManager::class);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());
        $this->object = TestObjectFactory::simple()->save();
        parent::setUp();
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
    public function response_is_invalidated_when_object_is_updated(): void
    {
        $this->cacheManager->invalidateTags(['o42'])
            ->willReturn($this->cacheManager->reveal());

        $this->client->request('GET', '/get-object?id=42');

        $this->object->setContent('Updated test content')->save();

        $this->cacheManager->invalidateTags(['o42'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
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
    public function response_is_invalidated_when_object_is_deleted(): void
    {
        $this->cacheManager->invalidateTags(['o42'])
            ->willReturn($this->cacheManager->reveal());

        $this->client->request('GET', '/get-object?id=42');

        $this->object->delete();

        $this->cacheManager->invalidateTags(['o42'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
    }
}
