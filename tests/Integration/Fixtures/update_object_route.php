<?php declare(strict_types=1);

use App\Controller\UpdateObjectController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $routes->add('update_object', '/update-object')
        ->controller(UpdateObjectController::class)
        ->methods(['POST']);
};
