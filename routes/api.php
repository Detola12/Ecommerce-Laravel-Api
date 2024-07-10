<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*          Category Routes          */
Route::post('/category/create', [\App\Http\Controllers\CategoryController::class, 'createCategory'])->middleware('auth:sanctum');
Route::patch('/category/edit/{id}', [\App\Http\Controllers\CategoryController::class, 'updateCategory'])->middleware('auth:sanctum');
Route::get('/category/all', [\App\Http\Controllers\CategoryController::class, 'getAllCategories']);
Route::delete('/category/remove/{id}', [\App\Http\Controllers\CategoryController::class, 'deleteCategory'])->middleware('auth:sanctum');

/*          Product Routes          */
Route::get('/products/all', [\App\Http\Controllers\ProductController::class, 'getAllProducts'])->middleware('auth:sanctum');
Route::post('/products/create', [\App\Http\Controllers\ProductController::class, 'createProduct'])->middleware('auth:sanctum');
Route::patch('/products/edit/{id}', [\App\Http\Controllers\ProductController::class, 'updateProduct'])->middleware('auth:sanctum');
Route::delete('/products/remove/{id}', [\App\Http\Controllers\ProductController::class, 'deleteProduct'])->middleware('auth:sanctum');


/*          User Routes             */
Route::post('/users/register', [\App\Http\Controllers\UserController::class, 'register']);
Route::post('/users/login', [\App\Http\Controllers\UserController::class, 'login']);
Route::post('/users/logout', [\App\Http\Controllers\UserController::class, 'logout'])->middleware('auth:sanctum');
