<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Service\CancelInvalidationListener;
use App\Service\ClearRuntimeListener;
use App\Service\InvalidateAdditionalTagListener;
use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;

return function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', '../src/')
        ->exclude('../src/TestKernel.php}');

    $services->set(ClearRuntimeListener::class)
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE])
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_DELETE])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_DELETE])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_DELETE]);

    $services->set(InvalidateAdditionalTagListener::class)
        ->public();

    $services->set(CancelInvalidationListener::class)
        ->public();
};
