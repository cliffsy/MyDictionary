<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/users'], function ($router) {
  $controller = UserController::class;

  $router->get('list', [$controller, 'list']);
  $router->get('{id}/show', [$controller, 'show']);
  $router->post('create', [$controller, 'create']);
  $router->put('{id}/update', [$controller, 'update']);
  $router->delete('{id}/delete', [$controller, 'delete']);
  $router->get('permissions', [$controller, 'getPermissions']);
  $router->get('roles', [$controller, 'getRoles']);
});
