<?php

use Illuminate\Support\Facades\Route;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function () {
    return 'Bem vindo!';
});

$router->group(['prefix' => 'api'], function ($router) {
    // Rotas públicas (não requerem autenticação)
    $router->post('login', 'Auth\AuthController@login');
    $router->post('logout', 'Auth\AuthController@logout');
    $router->post('refresh', 'Auth\AuthController@refresh');
    $router->post('user/criar-usuario', 'UserController@createUser');

    
    // Rotas protegidas por autenticação
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        // Rotas para UserController
        $router->get('me', 'UserController@me');
        $router->get('users', 'UserController@getUsers');
        $router->get('user/{user_id}', 'UserController@getUser');
        // ...

        // Rotas para TransactionController
        $router->post('transaction/depositar', 'TransactionController@deposit');
        $router->get('transaction/historico/{user_id}', 'TransactionController@getHistory');
        $router->post('transaction/transferir', 'TransactionController@transfer');
        // ...
    });
});