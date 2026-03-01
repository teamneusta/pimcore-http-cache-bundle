<?php declare(strict_types=1);

use App\Controller\WithoutAutomaticTaggingController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $routes->add('without_automatic_tagging', '/without-automatic-tagging')
        ->controller(WithoutAutomaticTaggingController::class);
};
