<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Victim;
use App\Models\Suspect;
use App\Models\Media;
use App\Models\Form;
use App\Models\Stamp;
use App\Models\VictimUpdate;

class AdminController extends BaseController
{

    public function index($request, $response)
    {

        // $users = $this->db->table("users")->get();    
        $users = User::all();
        $victims = Victim::count();
        $victimupdates = VictimUpdate::orderByDesc('date')->take(8)->get();
        $suspects = Suspect::count();
        return $this->view($response, 'home/' . 'index.twig.html',compact('victimupdates','users',"victims","suspects","applications"));
    }

    public function show($request, $response, $id)
    {
        $user = User::find($id);
        return $this->view($response, 'user/' . 'show.twig.html', ['user' => $user]);

        /*
        $payload = json_encode($user);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(201);*/
    }

    public function edit($request, $response, $id)
    {

        $method = $request->getMethod();
        if ($method == "POST") {
            return $response;
            
        } else {
            $user = User::find($id);
            return $this->view($response, 'user/' . 'show.html.twig', ['user' => $user]);
        }
    }

    public function logout($request, $response, \App\Auth\Auth $auth)
    {
        $auth->logout();
        return redirect()->route('admin');
    }

    public function maintenance($request, $response){
        
        global $app;
        $basePath = $app->getBasePath();
        $path = config_path('app.php');       
        $settings = include $path;

        $settings['maintenance'] = true;
        // Save the updated array back to app.php       
        file_put_contents($path, '<?php return ' . var_export($settings, true) . ';');

        echo 'Maintenance mode set to true.';

        $response->getBody()->write( json_encode("Maintenance mode set"));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(201);

    }

    public function refactorMedia($request, $response){
        global $app;
        $basePath = $app->getBasePath();
        
       

        //$this->flash->addMessage('success', json_encode($success));
        //return json_encode($success);
      
        $response->getBody()->write( json_encode("Success"));
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    public function settings($request, $response)
    {

        $method = $request->getMethod();
        if ($method == "POST") {
            return $response;
            
        } else {
            //$user = User::find($id);
            return $this->view($response, 'admin/' . 'settings.html.twig');
        }
    }
}
