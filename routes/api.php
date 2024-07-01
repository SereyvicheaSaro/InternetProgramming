<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController; 
use App\Http\Controllers\OrderController; 
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Authorization;
 
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
    Route::post('/allUser', [AuthController::class, 'allUser'])->middleware('auth:api')->name('allUser');
});

Route::group([
    'middleware'=>'api',
    'prefix'=>'categories'
],  function(){
    Route::post('/', [CategoryController::class, 'create'])->middleware(Authorization::class . ':admin');
    Route::get('/', [CategoryController::class, 'get'])->middleware(Authorization::class . ':admin,staff');
    Route::get('/{id}', [CategoryController::class, 'getById'])->middleware(Authorization::class . ':admin,staff');
    Route::patch('/{id}', [CategoryController::class, 'update'])->middleware(Authorization::class . ':admin');
    Route::delete('/{id}', [CategoryController::class, 'delete'])->middleware(Authorization::class . ':admin');
    Route::get('/{id}/products', [CategoryController::class, 'getAllProductsInCategory'])->middleware(Authorization::class . ':admin,staff');
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'products'
], function () {
    Route::post('/', [ProductController::class, 'create'])->middleware(Authorization::class . ':admin');
    Route::get('/', [ProductController::class, 'get'])->middleware(Authorization::class . ':admin,staff');
    Route::get('/{id}', [ProductController::class, 'getById'])->middleware(Authorization::class . ':admin,staff');
    Route::patch('/{id}', [ProductController::class, 'update'])->middleware(Authorization::class . ':admin');
    Route::delete('/{id}', [ProductController::class, 'delete'])->middleware(Authorization::class . ':admin');
});


Route::post('/orders', [OrderController::class, 'store']);
Route::get('/total-sales', [OrderController::class, 'totalSales']);
Route::get('/total-items', [OrderController::class, 'totalItemsOrdered']);