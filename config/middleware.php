<?php

declare(strict_types=1);

use Slim\App;
use App\Support\Middleware\TwigRouteMiddleware;
use App\Support\Middleware\MaintenanceModeMiddleware;
use Slim\Views\TwigMiddleware;

return static function (App $app) {

    $maintenanceMode = false;

    $app->add(new TwigRouteMiddleware( $app,Slim\Views\Twig::class));
    $app->add(TwigMiddleware::createFromContainer($app, Slim\Views\Twig::class));
      // Register the middleware for Maintenance Mode
  //  $app->add(new MaintenanceModeMiddleware($maintenanceMode, $app->getContainer()->get(Slim\Views\Twig::class), ['login','signin'] ));

    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(true, true, true);
};
