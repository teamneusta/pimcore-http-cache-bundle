<?php

declare(strict_types=1);

use App\Controller\GetObjectController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('get_object', '/get-object')
        ->controller(GetObjectController::class);
};
