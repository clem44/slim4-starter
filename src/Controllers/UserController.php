<?php

declare(strict_types=1);

namespace App\Controllers;


use App\Models\User;
use App\Models\Country;

class UserController extends BaseController
{
    public function index($request, $response)
    {
        $users = User::all();      
        return $this->view($response, 'user/' . 'index.twig.html',compact('users'));
    }

    public function show($request, $response, $id)
    {
        $user = User::find($id);
        return $this->view($response, 'user/' . 'show.twig.html', ['user' => $user]);

    }
    
    public function create($request, $response)
    {

        $username = $username_err= $password =$title= $confirm_password = "";
        $method = $request->getMethod();
        $countries = Country::all();
        if ($method == "POST") {
            //create the user
            $pattern = "/^['_a-z0-9-]+(\.['_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
            $postd = $request->getParsedBody();
            // Validate username
            if (empty(trim($postd["user_id"]))) {
                $username_err = "Please enter a user_id";
            } elseif (!preg_match($pattern, trim($postd["user_id"]))) {
                $username_err = "Username can only contain letters, numbers, and underscores.";
            } else {

                $usrExist = User::where('user_id',strtolower(trim($postd["user_id"])))->first();
                if (!empty($usrExist)) {
                    //dump($usrExist);
                    $username_err = "This username is already taken.";
                } else {
                    $username = strtolower(trim($postd["email"]));
                }
                // Validate password
                if (empty(trim($postd["password"]))) {
                    $password_err = "Please enter a password.";
                } elseif (strlen(trim($postd["password"])) < 6) {
                    $password_err = "Password must have atleast 6 characters.";
                } else {
                    $password = trim($postd["password"]);
                }

                // Validate confirm password
                if (empty(trim($postd["confirm_password"]))) {
                    $confirm_password_err = "Please confirm password.";
                } else {
                    $confirm_password = trim($postd["confirm_password"]);
                    if (empty($password_err) && ($password != $confirm_password)) {
                        $confirm_password_err = "Password did not match.";
                    }
                }
            }

            $usr = new user();
            $usr->user_id = $postd["user_id"];
            $usr->fname = $postd["fname"];
            $usr->lname = $postd["lname"];
            $usr->mname = $postd["mname"];
            $usr->user_type = $postd["user_type"];
            $usr->user_type = $postd["account_type"];

            $usr->save();

            return $this->view($response, 'user/' . 'create.twig.html', ['success' => "Service created successfully", 'countries' => $countries]);
            //return $response;
            
        } else {
           
            return $this->view($response, 'user/' . 'create.twig.html',compact('countries'));
        }
    }

    public function edit($request, $response, $id)
    {

        $method = $request->getMethod();
        if ($method == "POST") {
            return $response;
            
        } else {
            $user = User::find($id);
            return $this->view($response, 'user/' . 'edit.twig.html', ['user' => $user]);
        }
    }
}
