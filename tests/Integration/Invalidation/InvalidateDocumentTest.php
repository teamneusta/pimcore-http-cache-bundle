<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Invalidation;

use FOS\HttpCacheBundle\CacheManager;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Pimcore\Model\Document;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class InvalidateDocumentTest extends ConfigurableWebTestcase
{
    use ProphecyTrait;
    use ResetDatabase;

    private KernelBrowser $client;

    /** @var ObjectProphecy<CacheManager> */
    private $cacheManager;

    private Document $document;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->cacheManager = $this->prophesize(CacheManager::class);
        self::getContainer()->set('fos_http_cache.cache_manager', $this->cacheManager->reveal());
        $this->document = TestDocumentFactory::simplePage()->save();
        parent::setUp();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function response_is_invalidated_when_document_is_updated(): void
    {
        $this->cacheManager->invalidateTags(['d42'])
            ->willReturn($this->cacheManager->reveal());

        $this->client->request('GET', '/test_document_page');

        $this->document->setKey('updated_test_document_page')->save();

        $this->cacheManager->invalidateTags(['d42'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    #[ConfigureExtension('neusta_pimcore_http_cache', [
        'elements' => [
            'assets' => false,
            'documents' => true,
            'objects' => false,
        ],
    ])]
    public function response_is_invalidated_when_document_is_deleted(): void
    {
        $this->cacheManager->invalidateTags(['d42'])
            ->willReturn($this->cacheManager->reveal());

        $this->client->request('GET', '/test_document_page');

        $this->document->delete();

        $this->cacheManager->invalidateTags(['d42'])->shouldHaveBeenCalled();
        $this->cacheManager->flush()->shouldHaveBeenCalled();
    }
}
