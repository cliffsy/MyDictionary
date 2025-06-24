<?php

use App\Http\Controllers\DictionaryController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/dictionary'], function ($router) {
  $controller = DictionaryController::class;

  $router->get('search', [$controller, 'search']);
});
