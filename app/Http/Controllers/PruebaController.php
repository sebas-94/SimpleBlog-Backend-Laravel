<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//Modelos
use App\Post;
use App\Category;

class PruebaController extends Controller
{

    //Test ORM
    public function testOrm()
    {
        //Todos los posts
       /* $posts = Post::all();
        foreach ($posts as $post) {
            echo "<h1>{$post->title}</h1>";
            echo "<span>@{$post->user->name} - {$post->category->name}</span><br>";
            echo "<p>{$post->content}</p>";
            echo "<hr>";
        };*/

        //Todos las categorías
        $categories = Category::all();
        foreach ($categories as $category) {
            echo "<h1>{$category->name}</h1>";

            foreach ($category->posts as $post) {
                echo "<h4>{$post->title}</h4>";
                echo "<span>@{$post->user->name} - {$post->category->name}</span><br>";
                echo "<p>{$post->content}</p>";
                echo "<hr>";
            };

            echo "<br><br>";
        };


        die();
    }

    //Mi función
    public function index()
    {
        $titulo = 'Animales';
        $animales = ['perro', 'gato', 'vaca'];

        return view('prueba', [
            'titulo' => $titulo,
            'animales' => $animales
        ]);
    }

}
