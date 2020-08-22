<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * CLASES
 */
use App\Http\Middleware\ApiAuthMiddleware;

// RUTAS DE PRUEBA
Route::get('/', function () {
    return view('welcome');
});

Route::get('/animales', 'PruebaController@index');

Route::get('/testOrm', 'PruebaController@testOrm');

/*
 * RUTAS DEL API
 */

// Test's
Route::get('user/test', 'UserController@test');
Route::get('category/test', 'CategoryController@test');
Route::get('post/test', 'PostController@test');

// Rutas contolador User
Route::post('api/register', 'UserController@register');
Route::post('api/login', 'UserController@login');
Route::put('api/user/update', 'UserController@update');
Route::post('api/user/upload', 'UserController@upload')
    -> middleware(ApiAuthMiddleware::class);
Route::get('api/user/avatar/{filename}', 'UserController@getImage');
Route::get('api/user/detail/{id}', 'UserController@getUserDetail');

// Rutas contolador Category
Route::resource('/api/category', 'CategoryController');

// Rutas contolador Post
Route::resource('/api/post', 'PostController');
Route::post('api/post/upload', 'PostController@upload'); //Ya utiliza el middleware
Route::get('api/post/image/{filename}', 'PostController@getImage'); // Tiene excepción
Route::get('api/post/category/{id}', 'PostController@getPostsByCategory'); // Tiene excepción
Route::get('api/post/user/{id}', 'PostController@getPostsByUser'); // Tiene excepción
