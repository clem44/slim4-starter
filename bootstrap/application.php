<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use \DI\Bridge\Slim\Bridge as SlimAppFactory; //DI allows for direction injection into controller methods
use App\Helpers\CsrfTwigExtension;
use Slim\Csrf\Guard;

require __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_ENV', $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'DEVELOPMENT');
$settings = (require __DIR__ . '/../config/settings.php')(APP_ENV);

// Set up dependencies
$containerBuilder = new ContainerBuilder();
if($settings['di_compilation_path']) {
    $containerBuilder->enableCompilation($settings['di_compilation_path']);
}

//$dir = dirname(dirname(__FILE__))  . '/src/Resources/lang';
//dump($dir);
//exit();

// Set up dependencies
(require __DIR__ . '/../config/dependencies.php')($containerBuilder, $settings);

// Create app
$container = $containerBuilder->build();
$app =  SlimAppFactory::create($container);
$app->setBasePath("");

//AppFactory::setContainer($containerBuilder->build());
//$app = AppFactory::create();

// Register routes
(require __DIR__ . '/../config/routes.php')($app);

// Register middleware
(require __DIR__ . '/../config/middleware.php')($app, $settings);

$app->getContainer()->get('db');

//dump($app->getContainer()->get(Slim\Views\Twig::class));
// Register extention FLash Messages
$container->set('flash', function () {
    return new Slim\Flash\Messages();
});

$responseFactory = $app->getResponseFactory();

// Register CSRF prottection On Container
$container->set('csrf', function () use ($responseFactory, $app) {
    $guard = new Guard($responseFactory);
    $guard->setPersistentTokenMode(true);
    $twig = $app->getContainer()->get(Slim\Views\Twig::class);
    $twig->addExtension(new CsrfTwigExtension($guard));
    return $guard;
});
$app->add('csrf');

$flash = $app->getContainer()->get('flash');
$environment = $app->getContainer()->get(\Slim\Views\Twig::class)->getEnvironment();
$environment->addGlobal('flash', $flash);
$environment->addGlobal('auth',$app->getContainer()->get(\App\Auth\Auth::class));

$_SERVER['app'] = &$app;

if (!function_exists('app')) {
    function app()
    {
        return $_SERVER['app'];
    }
}

// Assign matched route arguments to Request attributes for PSR-15 handlers
//$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestHandler(true));
//echo "Application end page";
// Run application 

$app->run();
