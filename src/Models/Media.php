<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{

    protected $table = 'media';

    protected $fillable = ['title', 'name', 'published', 'slug', 'thumbnail', 'path', 'size', 'sizeunit', 'description', 'caption', 'type', 'extension'];

    protected $casts = ['published' => 'boolean','created_at'=>'datetime:Y-m-d H:00'];

    /*protected $dateFormat = 'Y-m-d';*/

   /* public function categories()
    {
        return $this->belongsToMany(category::class);
    }*/

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function stamps()
    {
        return $this->hasMany(Stamp::class);
    }

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

 
}
