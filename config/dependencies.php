<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Auth\Auth;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use App\Helpers\ConfigExtension;
use App\Helpers\MyTwigHelpers;
use Twig\Extra\String\StringExtension;

return static function (ContainerBuilder $containerBuilder, array $settings) {
    $containerBuilder->addDefinitions([
        'settings' => $settings,

        'db' => function (ContainerInterface $c) use ($settings) {
            $capsule = new Manager;
            $capsule->addConnection($settings['db']);
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        },

        
        Auth::class => function (ContainerInterface $c) {
            return new Auth();
        },


        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        Slim\Views\Twig::class => function (ContainerInterface $c) use ($settings) {
            //this class inherits from Twig templating engine.
            $twig = \Slim\Views\Twig::create($settings['views']['path'], ['cache' =>  false,'debug' => true]);
            //$twig = \Slim\Views\Twig::create('.');
            //$twig->getEnvironment()->addGlobal('messages', $c->flash->getMessages());//initialized in bootstrap.php
            $twig->addExtension(new ConfigExtension());
            $twig->addExtension(new MyTwigHelpers($c));
            $twig->addExtension(new StringExtension());
            $twig->addExtension(new \Twig\Extension\DebugExtension());
            return $twig;
        },
    ]);
};
