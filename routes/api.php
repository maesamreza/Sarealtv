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
Route::post('update/client/profile',[ClientController::class,'updateProfile'])->name('client.update');

});
Route::group( ['prefix' => 'client','middleware' => ['auth:client-api','scopes:client'] ],function(){
    // authenticated staff routes here 
    Route::get('dashboard',[ClientController::class, 'ClientDashboard']);
});

Route::post('client/login',[ClientController::class,'login'])->name('ClientLogin');
Route::post('client/register',[ClientController::class,'register'])->name('Clientreister');


Route::post('admin/Login',[adminController::class,'adminLogin'])->name('adminLogin');
Route::post('admin/register',[adminController::class,'adminregister'])->name('adminregister');

Route::group( ['prefix' => 'admin','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
    // authenticated staff routes here 
    Route::get('get/user',[adminController::class,'getUserDetails']);
    Route::get('dashboard',[adminController::class, 'adminDashboard']);
});
Route::post('adminlogout',[adminController::class,'adminlogout'])->name('adminlogout');

