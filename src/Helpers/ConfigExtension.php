<?php

declare(strict_types=1);

namespace App\Helpers;

use Slim\Csrf\Guard;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConfigExtension extends AbstractExtension
{

    /**
     * @var Array
     */
    protected $cf;

    public function getFunctions()
    {
        return [
            new TwigFunction('config', [$this, 'configFinder']),
        ];
    }

    public function configFinder($path =null)
    {

       $val=  config($path);
      
        return $val;
    }

}
