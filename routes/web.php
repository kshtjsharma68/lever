<?php

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

// Authentication testing 
// $router->get('/{route:.*}/', function () use ($router) {
//     return view('index');
// });
// $router->post('/check-proposal-password', 'ApiController@checkProposalPassword');


//Basic functionality
// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });

$router->get('/', 'ApiController@index');
$router->get('/publish-site', 'ApiController@publishSite');
$router->get('/syncUnpublish', 'ApiController@syncUnpublish');
