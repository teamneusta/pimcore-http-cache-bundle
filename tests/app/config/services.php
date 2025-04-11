<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Service\ClearRuntimeListener;
use App\Service\InvalidateAdditionalTagListener;
use Neusta\Pimcore\HttpCacheBundle\Cache\CacheInvalidatorInterface;
use Neusta\Pimcore\HttpCacheBundle\Element\InvalidateElementListener;
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

    // TODO: Remove!
    $services->set(InvalidateElementListener::class)
        ->arg('$cacheInvalidator', service(CacheInvalidatorInterface::class))
        ->arg('$dispatcher', service('event_dispatcher'))
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_UPDATE, 'method' => 'onUpdated'])
        ->tag('kernel.event_listener', ['event' => AssetEvents::POST_DELETE, 'method' => 'onDeleted'])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_UPDATE, 'method' => 'onUpdated'])
        ->tag('kernel.event_listener', ['event' => DataObjectEvents::POST_DELETE, 'method' => 'onDeleted'])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_UPDATE, 'method' => 'onUpdated'])
        ->tag('kernel.event_listener', ['event' => DocumentEvents::POST_DELETE, 'method' => 'onDeleted']);
};
