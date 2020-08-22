<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Post extends Model
{
    protected $table = 'posts';

    // Campos rellenables
    protected $fillable = ['title', 'content', 'category_id', 'image'];


    // Relación ManyToOne
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    // Relación ManyToOne
    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
