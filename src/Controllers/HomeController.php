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
      
        return $this->twig->render($response, 'home/' . 'index.html.twig');
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

    public function dutycalculator($request, $response)
    {
        $json =  file_get_contents( root_path('public/items.json'));
        //dump(  $json);
        $items = json_decode($json,true);
        //dump($items);
        return $this->view($response, 'home/' . 'dutycalculator.html.twig', ['title' => "Duty calculator","items"=>$items]);
    }

    

    public function fees($request, $response)
    {
        return $this->view($response, 'home/' . 'fees.html.twig', ['title' => "Fees"]);
       
    }
    public function ems($request, $response)
    {
        return $this->view($response, 'home/' . 'expressmail.html.twig', ['title' => "Express Mail Service"]);
    }

    public function privatebox($request, $response)
    {
        return $this->view($response, 'home/' . 'privatebox.html.twig', ['title' => "Private Box"]);
    }

    public function parcelpost($request, $response)
    {
        return $this->view($response, 'home/' . 'parcel.html.twig', ['title' => "Parcel Post"]);
    }

    public function history($request, $response)
    {
        return $this->view($response, 'home/' . 'history.html.twig', ['title' => "History"]);
    }

    public function faq($request, $response)
    {
        $faqs = Faq::all();
        $this->categories = Category::orderByDesc('title')->take(5)->get();
        $popular = Post::take(3)->get();
        $archives = Post::selectRaw('year(created_at) year, monthname(created_at) month, count(*) published')
        ->groupBy('year','month')
        ->orderByRaw('min(created_at) desc')
        ->get()
        ->toArray();
        return $this->view($response, 'home/' . 'faq.html.twig', ['faqs'=>$faqs,'archives'=>$archives,'popular'=>$popular,'title' => "Faqs", 'categories'=>$this->categories]);
    }

    public function team($request, $response)
    {
        $this->categories = Category::orderByDesc('title')->take(8)->get();
        return $this->view($response, 'home/' . 'team.html.twig', ['title' => "Team", 'categories'=>$this->categories]);
    }

    public function tracking($request, $response)
    {
        return $this->view($response, 'home/' . 'tracking.html.twig', ['title' => "History"]);
    }

    public function portal($request, $response)
    {

        return $this->view($response, 'home/' . 'portal.html.twig', ['title' => "Contact"]);
    }

    public function staff($request, $response)
    {
        return $this->view($response, 'home/' . 'staff.html.twig', ['title' => "Staff"]);
    }

    public function philatelic($request, $response)
    {
        return $this->view($response, 'home/' . 'philatelic.html.twig', ['title' => "philatelic"]);
    }
    

    public function downloads($request, $response)
    {

        $docs = Media::where("type","document")->get();
       // dd($docs);
        return $this->view($response, 'home/' . 'documents.html.twig', ['documents'=>$docs,'title' => "document library"]);
    }

    public function terms($request, $response)
    {
        return $this->view($response, 'home/' . 'terms.html.twig', ['title' => "Terms of Use"]);
    }

    public function privacy($request, $response)
    {
        return $this->view($response, 'home/' . 'privacy.html.twig', ['title' => "Private Policy"]);
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
