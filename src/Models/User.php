<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{

    protected $table = 'tbl_user';

    protected $fillable = ['username', 'firstname', 'lastname', 'email', 'password','user_id'];

    public function userlogs()
    {
        return $this->hasMany(UserLog::class);
    }

    public function getFullname(){
        return $this->fname ." ".$this->mname ." ". $this->lname;
    }

    public function addLog($msg = null)
    {
        $log = new UserLog();
        $log->message = $this->email ." : ".$msg;
        $log->username = $this->email;
        $this->userlogs()->save($log);

        //$log->user_id = $this;
        //$log->save();

    }

    public function victims()
    {
        return $this->hasMany(Victim::class,'social_worker');
    }

    public function role(){
        return $this->hasOne(Role::class);
    }

    public static function currentUser()
    {

        if (!empty($_SESSION["authuser"])) {
            return $_SESSION["authuser"];
        }
        return null;
    }
}
