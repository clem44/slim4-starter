<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Slim\Routing\RouteContext;


class AuthController extends BaseController
{
    protected $logger;
    protected $twig;

    public function login($request, $response )
    {

        $username = $password = $confirm_password = "";
        $username_err = $password_err = $confirm_password_err = "";
        //dump($request);
        //dump($request->getQueryParams());
        if($request->getMethod() == "POST"){

            return $this->view($response, 'auth/' . 'login.twig.html',['username'=>$username]);

        }else{

            //$username=  $request->getQueryParams()['username'] ?? '';
            return $this->view($response, 'auth/' . 'login.twig.html', $request->getQueryParams());
        }

       
    }

    public function register($request, $response)
    {
        $username = $username_err= $password =$title= $confirm_password = "";

        if($request->getMethod() == "POST"){

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
            // Check input errors before inserting in database
            if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

                // Prepare an insert statement

                $user = new User();
                //$user->username = $username;
                $user->password =  password_hash($password, PASSWORD_DEFAULT);
                //$user->reset = $postd['reset'];
                $user->email = $username;
                $user->firstname = $postd['firstname'];
                $user->lastname =  $postd['lastname'];
               /* if (isset($_POST['role'])) {
                    $role = R::load('role', intval($_POST['role']));
                    $user->sharedRoleList[] = $role;
                }*/
               //$user->lockedout = 0;
                $user->remember = 0;
                $user->save();
                $success = "User created successfully";
                $user->addLog("registered new user - " . $user->username);
                return $this->view($response, 'auth/' . 'confirmation.html.twig', ['title' => "Registered"]);
                //$this->view("./views/" . $this->controllerName . "/create.html", compact("user", "success"));
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
                //$roles = R::findAll("role", 'ORDER BY name ASC');
                return $this->view($response, 'auth/' . 'register.twig.html', compact("username_err", "password_err", "confirm_password_err"));
            }
           
        }else{
            return $this->view($response, 'auth/' . 'register.twig.html', compact("username_err", "password_err", "confirm_password_err","title"));
        }
     
    }

    public function signin($request, $response)
    {
        // CSRF protection successful if you reached this far.
        // dd($request);
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        //$url = $routeParser->urlFor('login');
        //$url = $routeParser->urlFor('login',);
      
        $postd = $request->getParsedBody();
        $username = $password = $confirm_password = "";
        $username_err = $password_err = $confirm_password_err = "";

        // Processing form data when form is submitted
        // Check if username is empty
        if (empty(trim($postd['username']))) {
            $username_err = "Please enter username.";
        } else {
            $username = strtolower(trim($postd['username']));
        }

        // Check if password is empty
        if (empty(trim($postd['password']))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($postd['password']);
        }
        // dump($username);
        // dump($password);
        // Validate credentials
        if (empty($username_err) && empty($password_err)) {
            $usrExist = User::where('user_id', $username)->first();
            //dump($usrExist);
            if (!empty($usrExist)) {

                //Verify password
                if($password == $usrExist->password){                
            
               /* if (password_verify($password, $usrExist->password)) {*/
                    //Check if password needs to be reset
                    $_SESSION["authuser"] = $usrExist;
                    $_SESSION["email"] = $username;

                    //dump($usrExist);
                    if ($usrExist->reset) {
                        $this->Reset($response);
                    }
                    // Password is correct, so start a new session
                    // Store data in session variables
                    //$usrExist->active = 1;
                    $_SESSION["loggedin"] = true;
                    $usrExist->addLog("Logged into application");
                    //$this->generateFormHash($username);

                    $_SESSION["last_login_timestap"] = time();
                    $_SESSION['expire'] = $_SESSION["last_login_timestap"] + (30 * 60); //30 mins

                    if (isset($_SESSION['redirecturl']))
                        // Redirect user to welcome page
                        //header("location: " . $_SESSION['redirecturl']);
                        header("location: " .$routeParser->urlFor($_SESSION['redirecturl'],['authuser'=> $usrExist] ) );
                    else{
                        return $response->withHeader('Location', $routeParser->urlFor('admin',['authuser'=> $usrExist] ));
                        //return $this->view($response, 'admin/index.html.twig');
                    }
                    //dump($_SESSION);
                    exit();
                } else {
                    // Password is not valid, display a generic error message
                    $this->logger->info('User password did not match');
                    $password_err = "Invalid username or password.";
                    $this->flash->addMessage('error', 'Invalid username or password.');
                }
            } else {
                $this->logger->info('Username did not match');
                $username_err = "Username or user does not exist";
                $this->flash->addMessage('error', 'Username or user does not exist.');
            }
            //dump($routeParser->urlFor('login', ['test'=>'test']));
            //return $response->withRedirect($routeParser->urlFor('login',[], compact('username_err','password_err', 'username', 'password')) );
            return $response->withHeader('Location',$routeParser->urlFor('login',[], compact('username_err','password_err', 'username')));
            //return app()->redirect($routeParser->urlFor('login',[],compact('username_err','password_err', 'username', 'password')));
            // return $this->view($response, 'auth/login.html.twig', compact('username_err','password_err', 'username', 'password'));
        }
    }


    public function Reset($response)
    {
        //require_once "views/login.php";
        $username = $_SESSION["email"];
        $currentuser = User::currentUser();
        $this->view($response, "auth/reset-password.html.twig", compact("username", "currentuser"));
        exit();
    }

    
}
