<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Neusta\Pimcore\HttpCacheBundle\CacheActivator;
use Neusta\Pimcore\HttpCacheBundle\Tests\Integration\Helpers\ClearRuntimeCacheListener;
use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;

return function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', '../src/')
        ->exclude('../src/TestKernel.php');

    $services->set(ClearRuntimeCacheListener::class)
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE])
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_DELETE])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_DELETE])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_DELETE]);

    $services->set(CacheActivator::class)
        ->public();
};
