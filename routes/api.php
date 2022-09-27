<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Sarealtv\ClientController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('AdminOrClient')->group(function(){
Route::post('update/client/profile/{id?}',[ClientController::class,'updateProfile'])->name('client.update');
Route::post('add/media/{clientId?}',[App\Http\Controllers\Sarealtv\ClientMediaController::class,'addMedia']);


});
Route::group( ['prefix' => 'client','middleware' => ['auth:client-api','scopes:client'] ],function(){
    // authenticated staff routes here 
    Route::get('dashboard',[ClientController::class, 'ClientDashboard']);
});

Route::post('client/login',[ClientController::class,'login'])->name('ClientLogin');
Route::post('client/register',[ClientController::class,'register'])->name('Clientreister');


Route::post('admin/login',[adminController::class,'login'])->name('adminLogin');

Route::group( ['prefix' => 'admin','middleware' => ['Admin'] ],function(){
    // authenticated staff routes here 

     Route::post('/update/profile',[adminController::class,'updateProfile'])->name('adminregister');
    Route::get('get/user',[adminController::class,'getMyDetails']);
    Route::get('dashboard',[adminController::class, 'adminDashboard']);
});
Route::post('adminlogout',[adminController::class,'adminlogout'])->name('adminlogout');

