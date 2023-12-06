<?php

declare(strict_types=1);

namespace App\Support\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpNotFoundException;

class AuthMiddleware
{
    /**
     * @var ContainerInterface
     */
    protected $ci;

    public function __construct($container)
    {
        $this->ci =  $container;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): Response
    {
        $container = $this->ci;
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }

        if (!$container->get(\App\Auth\Auth::class)->check()) {
            $container->get('flash')->addMessage('warning', 'Log in to Continue. User not Authenticated');
            $_SESSION['redirecturl'] = $route->getName();

            return redirect()->route('login');
        }
        //dd($route);
        $response = $handler->handle($request);
        return $response;
    }
}
