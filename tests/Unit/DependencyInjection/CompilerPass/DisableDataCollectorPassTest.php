<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass\DisableDataCollectorPass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DisableDataCollectorPassTest extends TestCase
{
    use ProphecyTrait;

    private DisableDataCollectorPass $disableCacheTagCollectionPass;

    protected function setUp(): void
    {
        $this->disableCacheTagCollectionPass = new DisableDataCollectorPass();
    }

    /**
     * @test
     */
    public function disables_data_collectors_when_profiler_is_not_enabled(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);

        $container->hasDefinition('profiler')->willReturn(false);

        $this->disableCacheTagCollectionPass->process($container->reveal());

        $container->removeDefinition('.neusta_pimcore_http_cache.collect_tags_response_tagger')
        ->shouldHaveBeenCalledOnce();
        $container->removeDefinition('neusta_pimcore_http_cache.data_collector')
        ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function does_not_disable_data_collectors_when_profiler_is_enabled(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);

        $container->hasDefinition('profiler')->willReturn(true);
        $this->disableCacheTagCollectionPass->process($container->reveal());

        $container->removeDefinition('.neusta_pimcore_http_cache.collect_tags_response_tagger')
        ->shouldNotHaveBeenCalled();
        $container->removeDefinition('neusta_pimcore_http_cache.data_collector')
        ->shouldNotHaveBeenCalled();
    }
}
