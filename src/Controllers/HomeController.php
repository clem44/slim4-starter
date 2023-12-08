<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Twig\Environment;
use App\Models\Media;
use App\Models\Service;
use App\Models\Post;
use App\Models\Product;
use App\Models\Stamp;
use App\Models\Faq;

class HomeController extends BaseController
{
    protected $logger;
    protected $twig;
    protected $db;


    public function index($request, $response)
    {
        // $this->logger->info('Home page controller dispatched');
        //$name = $request->getAttribute('name', 'world');
      
        return $this->twig->render($response, 'home/' . 'index.twig.html');
    }

    public function show($request, $response, $name)
    {

        /* $response->getBody()->write(
            $this->twig->render($response,'home/'.'index.html.twig', ['name' => $name])
        );
        return $response;*/
        return $this->view($response, 'home/' . 'index.html.twig', ['name' => $name]);
    }

    public function about($request, $response)
    {
        return $this->view($response, 'home/' . 'about.html.twig', ['title' => "About"]);
    }

    public function contact($request, $response)
    {

        return $this->view($response, 'home/' . 'contact.html.twig', ['title' => "Contact"]);
    }

    public function test($request, $response)
    {
        $key = "app.phone";
        dump(config($key));
        return $response;
    }
}
