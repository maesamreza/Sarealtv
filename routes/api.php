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

// Route::middleware('AdminOrClient')->get('/user', function (Request $request) {
//     return $request->user();
// });
//Route::get('get/file/{fileURL}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'getFileByUrl']);



Route::get('fetch/all/media/{clientId?}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'fetchAllMedia']);
Route::get('fetch/{ownerId}/media/like/by/{clientId?}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'fetchAllMediaLiked']);
Route::get('fetch/{ownerId}/media/like/{clientId?}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'fetchAllMediaILike']);
Route::get('get/media/info/{mediaId}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'getMediaById']);

Route::post('email/{type}',[App\Http\Controllers\Api\AdminController::class,'emailVerify']);

Route::get('/user/{id}', [adminController::class, 'getMyDetailsById']);
Route::get('/search/user/{searchKey}', [adminController::class, 'findAccountByKey']);

Route::get('media/get/comments/{mediaId}', [App\Http\Controllers\Sarealtv\MediaComments::class, 'fetchComments']);

Route::middleware('AdminOrClient')->group(function () {
    Route::get('/user', [adminController::class, 'getMyDetails']);
    Route::post('update/client/profile/{id?}', [ClientController::class, 'updateProfile'])->name('client.update');
    Route::post('add/media/{clientId?}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'addMedia']);
    Route::get('get/media/{fileURL}', [App\Http\Controllers\Sarealtv\ClientMediaController::class, 'getFileByUrl']);

    Route::get('media/like/{mediaId}', [App\Http\Controllers\Sarealtv\MediaLikes::class, 'like']);
    Route::get('media/count/like/{mediaId}', [App\Http\Controllers\Sarealtv\MediaLikes::class, 'getLikes']);

    Route::post('media/add/comments/{mediaId}', [App\Http\Controllers\Sarealtv\MediaComments::class, 'addComment']);
    Route::delete('media/remove/comments/{id}', [App\Http\Controllers\Sarealtv\MediaComments::class, 'removeComment']);

    Route::post('media/add/comment/replay/{commentId}', [App\Http\Controllers\Sarealtv\CommentsReplay::class, 'addCommentReplay']);
    Route::delete('media/remove/comment/replay/{id}', [App\Http\Controllers\Sarealtv\CommentsReplay::class, 'removeCommentReplay']);
    Route::get('media/get/comment/replay/{commentId}', [App\Http\Controllers\Sarealtv\CommentsReplay::class, 'fetchCommentReplays']);

    Route::post('follow/{clientId}', [App\Http\Controllers\Sarealtv\Followers::class, 'follow']);
    Route::post('unfollow/{clientId}', [App\Http\Controllers\Sarealtv\Followers::class, 'unFollow']);
    Route::post('get/followers/{clientId?}', [App\Http\Controllers\Sarealtv\Followers::class, 'followers']);
    Route::post('get/you/following/{clientId?}', [App\Http\Controllers\Sarealtv\Followers::class, 'following']);
    Route::post('get/follow/requests/{clientId?}', [App\Http\Controllers\Sarealtv\Followers::class, 'followRequests']);
    Route::post('get/following/requests/{clientId?}', [App\Http\Controllers\Sarealtv\Followers::class, 'followingRequests']);
    Route::post('accept/follow/{id}', [App\Http\Controllers\Sarealtv\Followers::class, 'acceptFollowRequest']);
    Route::post('delete/follow/{id}', [App\Http\Controllers\Sarealtv\Followers::class, 'deleteFollowRequest']);

    // Route::delete('media/remove/comment/replay/{id}',[App\Http\Controllers\Sarealtv\CommentsReplay::class,'removeCommentReplay']);
    // Route::get('media/get/comment/replay/{commentId}',[App\Http\Controllers\Sarealtv\CommentsReplay::class,'fetchCommentReplays']);


    // media List Bookmarks
    Route::post('list/add', [App\Http\Controllers\MediaBookmarkController::class, 'CreateList']);
    Route::post('list/remove/{listId}', [App\Http\Controllers\MediaBookmarkController::class, 'RemoveList']);
   
    Route::post('media/list/add/{mediaId}/{listId}', [App\Http\Controllers\MediaBookmarkController::class, 'AddToList']);
    Route::post('media/list/remove/{mediaId}/{listId}/{clientId?}', [App\Http\Controllers\MediaBookmarkController::class, 'RemoveFromList']);
    Route::get('media/list/fetch/{listId}/{clientId?}', [App\Http\Controllers\MediaBookmarkController::class, 'getList']);
    Route::get('{type}/list/fetch/{clientId?}', [App\Http\Controllers\MediaBookmarkController::class, 'fetchListNames']);
    
    //<--end-->


    // messages
    Route::post('message/send', [App\Http\Controllers\Messages::class, 'sendMessage']);
    Route::post('message/remove/{messageId}', [App\Http\Controllers\Messages::class, 'RemoveMessage']);
    Route::get('messages/{clientId}', [App\Http\Controllers\Messages::class, 'getMessageList']);
    Route::get('inbox/list', [App\Http\Controllers\Messages::class, 'fetchChatings']);
    //<--end-->




        



    Route::get('fetch/{type}/{cate?}', [App\Http\Controllers\Api\AdminMedia::class, 'fetchAllMedia']);
    Route::post('admin/media/add/comments/{mediaId}', [\App\Http\Controllers\Api\MediaComments::class, 'addComment']);
    Route::delete('admin/media/remove/comments/{id}', [\App\Http\Controllers\Api\MediaComments::class, 'removeComment']);
   
    Route::middleware('Admin')->group(function () {
        Route::get('list/client', [ClientController::class, 'ClientList']);
        Route::post('profile/{status}/{id:users}', [ClientController::class, 'setActive'])->name('client.active');
        Route::post('remove/client/profile/{id:users}', [ClientController::class, 'removeClient'])->name('client.remove');
        Route::post('admin/add/media', [App\Http\Controllers\Api\AdminMedia::class, 'addMedia']);
        Route::post('admin/get/cate', [App\Http\Controllers\Api\AdminMedia::class, 'getCate']);
        
        });

});
Route::group(['prefix' => 'client', 'middleware' => ['auth:client-api', 'scopes:client']], function () {
    // authenticated staff routes here 
    Route::get('dashboard', [ClientController::class, 'ClientDashboard']);
});

Route::post('client/login', [ClientController::class, 'login'])->name('ClientLogin');
Route::post('client/register', [ClientController::class, 'register'])->name('Clientreister');


Route::post('admin/login', [adminController::class, 'login'])->name('adminLogin');

Route::group(['prefix' => 'admin', 'middleware' => ['Admin']], function () {
    // authenticated staff routes here 
    Route::post('/update/profile', [adminController::class, 'updateProfile'])->name('admin.update');
    //Route::get('dashboard', [adminController::class, 'adminDashboard']);
});

//Route::post('adminlogout', [adminController::class, 'adminlogout'])->name('adminlogout');
