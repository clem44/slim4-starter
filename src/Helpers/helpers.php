<?php

use Slim\Psr7\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Helpers\Redirector;

if (!function_exists('view')) {
    function view(Response $response, $template, $with = [])
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../../src/views');
        $twig = new Twig\Environment($loader, [
            __DIR__ . '/../var/cache'
        ]);

        $response->getBody()->write(
            $twig->render($template, $with)
        );

        return $response;
    }
}


function url_for($name, $data = [], $queryParams = []) {
    global $app;
    //dump($app);

    return $app->getRouteCollector()->getRouteParser()->urlFor($name, $data, $queryParams);
}


function redirect($url = null) {
    
    return new Redirector($url);
}

if (!function_exists('root_path')) {
    function root_path($path = "")
    {
        return  realpath(__DIR__ . "/../../{$path}");
    }
}

if (!function_exists('asset_path')) {
    function asset_path($path = "")
    {
        return  root_path("public/{$path}");
    }
}


if (!function_exists('app_path')) {
    function app_path($path = "")
    {
        return root_path("src/{$path}");
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return root_path("config/{$path}");
    }
}

/**
 * Location helpers
 * 
 * @return public folder's path
 * 
 */
if (!function_exists('public_path')) {
    function public_path($path = "")
    {
        return root_path("public/{$path}");
    }
}

if (!function_exists('throw_when')) {
    function throw_when(bool $fails, string $message, string $exception = Exception::class)
    {
        if (!$fails) return;

        throw new $exception($message);
    }
}

if (!function_exists('config')) {
    function config($path = null, $value = null)
    {
        //$config = app()->resolve('config');
        $config = [];
        $folder = scandir(config_path());
        $config_files = array_slice($folder, 2, count($folder));

        foreach ($config_files as $file) {
            throw_when(
                substr($file, strpos($file, ".") + 1) !== 'php',
                "Config files must be .php files"
            );
            if ($file && !is_null($first = strtok($file, '.'))) {
                if ($first == strtok($path, '.')) {
                    $config = include(config_path($first . ".php") );
                }
            }
        }
        if (is_null($value)) {
            return data_get($config, substr($path, strpos($path, ".") + 1) );
        }

        data_set($config,$path, $value);
        //return data_set($config,$path);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {

            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && array_key_exists($target, $segment)) {
                $target = $target[$segment];

            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};

            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!is_array($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (is_array($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}
