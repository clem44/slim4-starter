<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyTwigHelpers extends AbstractExtension
{

    private $container;

    function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('asset_path', [$this, 'assetPath']),
            //new TwigFunction('base_path', [$this, 'basePath']),//twig already has this built in
            new \Twig\TwigFunction('is_route', [$this, 'isRoute']),

            new \Twig\TwigFunction('render_menu', [$this, 'renderMenu'])
        ];
    }

    public function isRoute($name)
    {
        // I need to retrieve the current route name here but the container does not have the updated request with 'route' attribute
        $current_route_name = $this->container->get('request')->getAttribute('route')->getName();
        return strtolower($name) === strtolower($current_route_name);
    }

    public function assetPath($path = null)
    {
      
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $url = "https://";
        else
            $url = "http://";
        // Append the host(domain name, ip) to the URL.   
        $url .= $_SERVER['HTTP_HOST'];
        $base = $this->container->get(\Slim\App::class)->getRouteCollector()->getBasePath();
        $url.= $base;    

        return  $url."/".$path;
    }

    /**
     * Render Menu tree
     * pass in the menuitem object here
     */
    public function renderMenu($parent_id = 0,$query =[])
    {
        // I need to retrieve the current route name here but the container does not have the updated request with 'route' attribute
        $items = '';
        //$query = $db->query("SELECT * FROM menu WHERE parent_id = ? ORDER BY id_menu ASC", $parent_id);

        if (!empty($query)) {
            $items .= '<ol class="dd-list">';
            $result = $query;
            foreach ($result as $row) {
                $items .= $this->renderMenuItem($row->id, $row->name, $row->url);
                $items .= $this->renderMenu($row['id'], $row->children);
                $items .= '</li>';
            }
            $items .= '</ol>';
        }
        return $items;
    }

    function renderMenuItem($id, $label, $url)
    {
        return '<li class="dd-item dd3-item" data-id="' . $id . '" data-label="' . $label . '" data-url="' . $url . '">' .
            '<div class="dd-handle dd3-handle" > Drag</div>' .
            '<div class="dd3-content"><span>' . $label . '</span>' .
            '<div class="item-edit">Edit</div>' .
            '</div>' .
            '<div class="item-settings d-none">' .
            '<p><label for="">Navigation Label<br><input type="text" name="navigation_label" value="' . $label . '"></label></p>' .
            '<p><label for="">Navigation Url<br><input type="text" name="navigation_url" value="' . $url . '"></label></p>' .
            '<p><a class="item-delete" href="javascript:;">Remove</a> |' .
            '<a class="item-close" href="javascript:;">Close</a></p>' .
            '</div>';
    
    }

}
