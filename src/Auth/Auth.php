<?php

namespace App\Auth;

use App\Models\User;

class Auth
{

    public function user()
    {
        if (isset($_SESSION['authuser'])) {
            return $_SESSION['authuser'];
            //return User::find($_SESSION['authuser']);
        }
        return null;
    }

    public function check()
    {
        return isset($_SESSION['authuser']);
    }

    public function attempt($email, $password)
    {

        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user->password)) {
            $_SESSION['authuser'] = $user;
            return true;
        }

        return false;

    }

    public function logout()
    {
        unset($_SESSION['authuser']);
    }

}