<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PrPoCerController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\FileController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users', [UserController::class, 'getAllUsers']);

Route::get('/loadAnswer', [PrPoCerController::class, 'getAllUsers']);

Route::post('/uploadImage', [ImageUploadController::class, 'uploadImage']);

Route::get('/loadListImage', [ImageUploadController::class, 'loadListImage']);

Route::delete('/deleteImage', [ImageUploadController::class, 'deleteImage']);

Route::get('/hello', [UserController::class, 'helloworld']);

Route::get('/hey', [UserController::class, 'hi']);

Route::get('/uploadImage', [FileController::class,'uploadImage']);