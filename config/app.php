<?php

return [
    'app_name'=>"Slim starterkit",
    'db' => [
        'driver' => 'mysql',
        'host' => '',
        'port' => 1433,
        'database' => '',
        'username' => 'root',
        'password' => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ],   
    'mail'=>[
        "driver"=>"smtp",
        "host"   => 'sandbox.smtp.mailtrap.io',  
        'username' => '',
        'password' => '', 
        'encryption'=>'tls',
        'port' => 2525,  
    ],   
    'recaptcha'=>[
        "secret"   => '',  
        "site"   => '',  
    ],
    'sqlite'=>[
        'driver' => 'sqlite',
        'database' => './database/aps_db.sqlite',
    ],
    'maintenance'=>false,
    'description'=>'We provide a modern, reliable, high quality communication and philatelic service through well trained and motivated staff.',
    'website'=>'',
    'company'=>'',
    'address'=>"",
    'phone'=>'',
    'fax'=>'',
    'facebook'=>'',
    'instagram'=>'',
    'email'=>''

];
