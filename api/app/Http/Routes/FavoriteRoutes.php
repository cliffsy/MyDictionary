<?php

use App\Http\Controllers\FavoritesController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/favorites'], function ($router) {
  $controller = FavoritesController::class;

  $router->get('/', [$controller, 'list']);
  $router->get('{word}', [$controller, 'show']);
  $router->post('/', [$controller, 'create']);
  $router->put('{id}', [$controller, 'update']);
  $router->delete('{id}', [$controller, 'delete']);
});
