<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\UserController;

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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('file/upload', [FileController::class, 'upload']);
    Route::post('file/rename', [FileController::class, 'rename']);
    Route::post('file/delete', [FileController::class, 'delete']);
    Route::post('file/download', [FileController::class, 'download']);
    Route::post('directory/create', [DirectoryController::class, 'createDirectory']);
    Route::post('directory/total', [DirectoryController::class, 'createDirectory']);
    Route::get('user/total', [UserController::class, 'total']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('token', [AuthController::class, 'getToken']);
