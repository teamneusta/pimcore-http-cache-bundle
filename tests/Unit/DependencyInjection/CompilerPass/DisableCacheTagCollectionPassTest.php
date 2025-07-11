<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass\DisableCacheTagCollectionPass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DisableCacheTagCollectionPassTest extends TestCase
{
    use ProphecyTrait;

    private DisableCacheTagCollectionPass $disableCacheTagCollectionPass;

    protected function setUp(): void
    {
        $this->disableCacheTagCollectionPass = new DisableCacheTagCollectionPass();
    }

    /**
     * @test
     */
    public function disables_cache_tag_collection_when_profiler_is_not_enabled(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);

        $container->hasDefinition('profiler')->willReturn(false);

        $this->disableCacheTagCollectionPass->process($container->reveal());

        $container->removeDefinition('.neusta_pimcore_http_cache.collect_tags_response_tagger')
        ->shouldHaveBeenCalledOnce();
        $container->removeDefinition('neusta_pimcore_http_cache.cache.data_collector.cache_tag_data_collector')
        ->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function does_not_disable_cache_tag_collection_when_profiler_is_enabled(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);

        $container->hasDefinition('profiler')->willReturn(true);
        $this->disableCacheTagCollectionPass->process($container->reveal());

        $container->removeDefinition('.neusta_pimcore_http_cache.collect_tags_response_tagger')
        ->shouldNotHaveBeenCalled();
        $container->removeDefinition('neusta_pimcore_http_cache.cache.data_collector.cache_tag_data_collector')
        ->shouldNotHaveBeenCalled();
    }
}
