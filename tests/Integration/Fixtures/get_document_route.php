<?php declare(strict_types=1);

use App\Controller\GetDocumentController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('get_document', '/get-document')
        ->controller(GetDocumentController::class);
};
