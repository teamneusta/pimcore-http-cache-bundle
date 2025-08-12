<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Configuration;

use Neusta\Pimcore\HttpCacheBundle\DataCollector;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ArrangeCacheTest;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\TestDocumentFactory;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureExtension;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableWebTestcase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

#[ConfigureExtension('neusta_pimcore_http_cache', [
    'elements' => [
        'documents' => true,
    ],
])]
#[ConfigureRoute(__DIR__ . '/../Fixtures/get_document_route.php')]
final class CollectConfigurationDataTest extends ConfigurableWebTestcase
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
    #[ConfigureExtension('framework', [
        'profiler' => [
            'enabled' => true,
            'collect' => true,
        ],
    ])]
    public function collects_configuration_data(): void
    {
        self::arrange(fn () => TestDocumentFactory::simplePage(5))->save();

        $this->client->request('GET', '/test_document_page');
        $this->client->enableProfiler();

        $dataCollector = $this->client->getProfile()->getCollector('pimcore_http_cache');

        self::assertInstanceOf(DataCollector::class, $dataCollector);
        self::assertSame(
            self::getContainer()->getParameter('neusta_pimcore_http_cache.config'),
            $dataCollector->getConfiguration(),
        );
    }

    /**
     * @test
     */
    #[ConfigureExtension('framework', [
        'profiler' => [
            'enabled' => false,
            'collect' => true,
        ],
    ])]
    public function does_not_collect_configuration_data_when_profiler_is_disabled(): void
    {
        self::arrange(fn () => TestDocumentFactory::simplePage(5))->save();

        $this->client->request('GET', '/test_document_page');
        $this->client->enableProfiler();

        self::assertFalse($this->client->getProfile());
    }
}
