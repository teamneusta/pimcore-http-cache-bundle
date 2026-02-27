<?php declare(strict_types=1);

use App\Controller\GetAssetController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $routes->add('get_asset', '/get-asset')
        ->controller(GetAssetController::class);
};
