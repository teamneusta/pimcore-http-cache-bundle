<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\Cache\DataCollector;

use Neusta\Pimcore\HttpCacheBundle\Cache\DataCollector\ConfigurationDataCollector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConfigurationDataCollectorTest extends TestCase
{
    use ProphecyTrait;

    private ConfigurationDataCollector $configurationDataCollector;

    protected function setUp(): void
    {
        $this->configurationDataCollector = new ConfigurationDataCollector(
            ['elements' => ['objects' => false, 'assets' => false, 'documents' => true]],
        );
    }

    /**
     * @test
     */
    public function collect_stores_configuration_data(): void
    {
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);

        $this->configurationDataCollector->collect(
            $request->reveal(),
            $response->reveal(),
        );

        self::assertSame(
            ['elements' => ['objects' => false, 'assets' => false, 'documents' => true]],
            $this->configurationDataCollector->getConfig(),
        );
    }

    /**
     * @test
     */
    public function getName_returns_expected_name(): void
    {
        self::assertSame('configuration', $this->configurationDataCollector->getName());
    }

    /**
     * @test
     */
    public function reset_clears_configuration_data(): void
    {
        $this->configurationDataCollector->reset();

        self::assertEmpty($this->configurationDataCollector->getConfig());
    }
}
