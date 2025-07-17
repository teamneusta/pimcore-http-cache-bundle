<?php declare(strict_types=1);

namespace Neusta\Pimcore\HttpCacheBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Neusta\Pimcore\HttpCacheBundle\DependencyInjection\CompilerPass\SetBundleConfigurationPass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class SetBundleConfigurationPassTest extends TestCase
{
    use ProphecyTrait;

    private SetBundleConfigurationPass $setBundleConfigurationPass;

    protected function setUp(): void
    {
        $this->setBundleConfigurationPass = new SetBundleConfigurationPass();
    }

    /**
     * @test
     */
    public function it_sets_bundle_configuration_in_configuration_data_collector(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);
        $definition = $this->prophesize(Definition::class);
        $configuration = ['elements' => ['objects' => false, 'assets' => false, 'documents' => true]];

        $container->hasDefinition('profiler')->willReturn(true);
        $container->getParameter('neusta_pimcore_http_cache.config')->willReturn($configuration);
        $container->getDefinition('neusta_pimcore_http_cache.cache.data_collector.configuration_collector')
            ->willReturn($definition->reveal());
        $definition->setArgument(0, Argument::any())->willReturn($definition);

        $this->setBundleConfigurationPass->process($container->reveal());

        $definition->setArgument(0, $configuration)->shouldHaveBeenCalledOnce();
    }

    /**
     * @test
     */
    public function it_does_not_set_configuration_when_profiler_is_not_enabled(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);
        $definition = $this->prophesize(Definition::class);

        $container->hasDefinition('profiler')->willReturn(false);

        $this->setBundleConfigurationPass->process($container->reveal());

        $definition->setArgument(0, Argument::any())->shouldNotHaveBeenCalled();
    }
}
