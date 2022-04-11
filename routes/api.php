<?php

use App\Http\Controllers\Api\ProductController as ApiProductController;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
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

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout',[AuthController::class,'logout']);

});

Route::prefix('product')->group(function () {
    Route::apiResource('products',ApiProductController::class);
});
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
