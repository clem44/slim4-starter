<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLog extends Model{

    protected $table = 'userlogs';

    protected $fillable = ['username','message','created_at', 'extra'];


    public function user(){

        return $this->belongsTo(User::class,'user_id');
    }

   
}