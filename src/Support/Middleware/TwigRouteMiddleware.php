<?php

namespace App\Support\Middleware;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handle;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use RuntimeException;
use Exception;


class TwigRouteMiddleware implements MiddlewareInterface
{
    protected Twig $twig;

    public function __construct(App $app,  string $containerKey = 'view')
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new RuntimeException('The app does not have a container.');
        }
        if (!$container->has($containerKey)) {
            throw new RuntimeException(
                "The specified container key does not exist: $containerKey"
            );
        }

        $twig = $container->get($containerKey);
        if (!($twig instanceof Twig)) {
            throw new RuntimeException(
                "Twig instance could not be resolved via container key: $containerKey"
            );
        }

        $this->twig = $twig;
    }

    /**
     *  This allows us to create a global redirect and global request input parser
     */
    public function process(Request $request, Handle $handler): ResponseInterface
    {
        //$route = routeContext::fromRequest($request)->getRoute();
        try {
            $route = RouteContext::fromRequest($request)->getRoute();

            if ($route === null) {
                return $handler->handle($request);
            }
            $this->twig->getEnvironment()->addGlobal('route_name', $route->getName());

        } catch (\Throwable $th) {
           // throw new Exception($th->message);
           // throw_when(true, $th);
           dd($th);
        }


        return $handler->handle($request);

        //throw_when(empty($route), "Route not found in request");
    }
}
