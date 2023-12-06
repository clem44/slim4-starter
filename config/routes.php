<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\AdminController;
use App\Support\Middleware\AuthMiddleware;

use Slim\App;

return function (App $app) {
   // $app->get('/[{name}]', HomePageHandler::class)->setName('home');

    $app->get('/phpinfo', function ($request, $response) {
        echo phpinfo();
    });

    //AUTH
    $app->get('/login', [AuthController::class, 'login'])->setName('login');
    $app->get('/register', AuthController::class . ':register')->setName('register.index');
    $app->post('/register', AuthController::class . ':register')->setName('register');
    $app->post('/signin', [AuthController::class, 'signin'])->setName('signin');

    $app->group('', function ($app) {

        $app->get('/', AdminController::class . ':index')->setName('admin.home');
        $app->get('/home', [AdminController::class, 'index'])->setName('admin');      
        $app->post('/logout', AdminController::class . ':logout')->setName('logout');
        $app->get('/settings', [AdminController::class, 'settings'])->setName('settings');

        $app->get('/refactordata', [AdminController::class, 'refactorMedia'])->setName('refactormedia');
        $app->get('/maintenance', [AdminController::class, 'maintenance'])->setName('maintenance');

    })->add(new AuthMiddleware($app->getContainer()));


    $app->group('/user/', function ($app) {

        $app->get('/all', UserController::class . ':index')->setName('users.all');
        $app->get('/show/{id}', UserController::class . ':show')->setName('user.show');
        $app->map(['GET', 'POST'], 'create', UserController::class . ':create')->setName('user.add');
        $app->map(['GET', 'POST'], '{id}/edit',UserController::class . ':edit')->setName('user.edit');
        $app->post('delete/{id}', [UserController::class, 'delete'])->setName('user.delete');

    })->add(new AuthMiddleware($app->getContainer()));
  
};
