<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login',[UserController::class,'login']);

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('logout',[UserController::class,'logout']);

    /*
    | --------------------------------------------------------------------------
    | User APIs
    | --------------------------------------------------------------------------
    */
    Route::post('user-table',[UserController::class,'getUserTable'])->name('users.table');
    Route::get('user-list',[UserController::class,'getUserList']);
    Route::post('user/{add}',[UserController::class,'addUpdateUser'])->name('users.add');  
    Route::post('user/{update}',[UserController::class,'addUpdateUser'])->name('users.update');  
    Route::post('user/delete',[UserController::class,'deleteUser'])->name('users.delete');  
    Route::post('user/get',[UserController::class,'getUser'])->name('users.get');  
    Route::post('user/get_details',[UserController::class,'getUserDetails'])->name('users.get_details'); 
});
