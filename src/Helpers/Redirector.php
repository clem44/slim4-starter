<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Response;

class Redirector
{
    protected $response;

    public function __construct(?string $url)
    {
        $this->response = new Response();

        if ($url) {
            $this->response = $this->response->withHeader('Location', $url);
            return $this->send();
        }

        return $this;
    }

    public function route(?string $name, $data =null)
    {
        if(!empty($data)){
           // print_r("data is empty");
            $url = url_for($name,$data);
           // print_r($url);
            $this->response = $this->response->withHeader('Location',$url);
        }else{
            //print_r("data is set");
            //$url = url_for($name);
            //print_r($url);
            $this->response = $this->response->withHeader('Location', url_for($name));
        }
        //dump($this->response);
       // exit();
        //$this->response = $this->response->withHeader('Location', url_for($name));

        return $this->send();
    }

    public function send()
    { 
        //dump($this->response);
        return $this->response->withStatus(302);
    }
}