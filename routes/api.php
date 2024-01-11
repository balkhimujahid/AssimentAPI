<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::POST('Create-user', [APIController::class, 'CreateUser']);
Route::GET('Get-user', [APIController::class, 'GetUser']);
Route::GET('Get-user-detail/{id?}', [APIController::class, 'GetUserDetail']);
Route::post('/forgotpassword', [APIController::class, 'forgotPassword']);
Route::post('resetPassword/{token}', [APIController::class, 'resetPassword']);

Route::POST('login', [APIController::class, 'login']);
