<?php

namespace App\Support\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig; // Import the Twig namespace

class MaintenanceModeMiddleware implements MiddlewareInterface
{
    private $maintenanceMode;
    protected Twig $twig; // Add a Twig instance variable
    private $allowedRoutes=[];

    public function __construct(bool $maintenanceMode, Twig $twig,array $allowedRoutes = [])
    {
        $this->maintenanceMode = $maintenanceMode;
        $this->twig = $twig; // Inject the Twig instance
        $this->allowedRoutes = $allowedRoutes;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {

         // Check if the current route has a name
         //$route = $request->getAttribute('__routingResults__');
        
        $routeName = $request->getAttribute('__route__')->getName();
        //var_dump($routeName);
 
         // Check if the route is in the list of allowed routes
         if (in_array($routeName, $this->allowedRoutes) && !isAdminLoggedIn() || $this->maintenanceMode === false) {
             return $handler->handle($request); // Allow access to the route
         } 
       /* if ($request->getUri()->getPath() === '/admin') {
            return $handler->handle($request); // Skip maintenance mode for "/admin"
        }*/        
        if ($this->maintenanceMode && !isAdminLoggedIn()) {
            
            $response = new \Slim\Psr7\Response();
            //$response->getBody()->write('The website is under maintenance. Please check back later.');
            //Render the maintenance Twig template
            $template = 'maintenance.html.twig';

            // Customize the response with the rendered Twig template
            $response = $this->twig->render($response, $template, [
                'message' => 'The website is under maintenance. Please check back later.',
            ]);


            return $response->withStatus(503)->withHeader('Retry-After', 3600); // Service Unavailable
        }

        // Continue with the next middleware or route handler
        return $handler->handle($request);
    }
}

function isAdminLoggedIn() {
    // Add your logic here to check if the user is logged in as an admin
    // Return true if the user is logged in as an admin, otherwise return false
    return isset($_SESSION['authuser']); // Assuming you store user roles in a session
}
