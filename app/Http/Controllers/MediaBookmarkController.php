<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Tools\Util;
use Validator;
use Illuminate\Validation\Rule;

class MediaBookmarkController extends Controller
{

    public function CreateList(Request $req)
    {
        $user = Util::getUserDetail();
        $rules = [
            'type' => 'required|string|in:Private,Public',
            'des' => 'nullable|string',
            'title' => 'required|string',
            'checkList' => ["required",'integer',Rule::unique('bookmark_lists','client_id')
            ->where('title',$req->title)]
        ];
        $checkInputs = Validator::make(array_merge($req->all(),['checkList' => $user->id]), $rules, [
            'checkList.exists' => 'This Title Allready In List'
        ]);
        if ($checkInputs->fails()) return response()->json([
            'status' => false,
            'message' => 'Inputs Not Valid!', 'errors' => $checkInputs->errors()
        ], 422);

        try {

            $user->BookmarkLists()->create([
                'type' => $req->type,
                'title' => $req->title,
                'des' => $req->des,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'List Created'
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }


    public function AddToList($mediaId,$listId)
    {

        $user = Util::getUserDetail();

        $rules = [
            'id' => 'required|integer|exists:client_media,id',
            'checkList' => ["required",'integer',Rule::unique('media_bookmarks','client_media_id')
            ->where('client_id',$user->id)]
        ];
        $checkInputs = Validator::make(['id' => $mediaId,
        'checkList' =>$mediaId], $rules, [
            'checkList.unique' => 'This Media Allready In List'
        ]);
        if ($checkInputs->fails()) return response()->json([
            'status' => false,
            'message' => 'Inputs Not Valid!', 'errors' => $checkInputs->errors()
        ], 422);
        try {

            $media = \App\Models\ClientMedia::select('id', 'client_id')->find($mediaId);
            $media->MediaList()->create(['owner_id' => $media->client_id, 'client_id' => $user->id]);
            return response()->json([
                'status' => true,
                'message' => 'Added to List'
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }

    public function RemoveFromList($mediaId, $clientId = false)
    {

        $user = Util::getUserDetail();
        $clientId=($user->role =='admin')?$clientId:$user->id;
        if(!$clientId)  return response()->json([
            'status' => false,
            'message' => 'Client Id Is Required'
        ], 422);

        $rules = [
            'id' => "required|integer|exists:media_bookmarks,client_media_id,client_id,$clientId"
        ];
        $checkInputs = Validator::make(['id' => $mediaId], $rules, [
            'checkList.exists' => 'This Media Not In List'
        ]);
        if ($checkInputs->fails()) return response()->json([
            'status' => false,
            'message' => 'Inputs Not Valid!', 'errors' => $checkInputs->errors()
        ], 422);

        try {

            \App\Models\MediaBookmark::where('client_id', $clientId)->where('client_media_id', $mediaId)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Removed From List'
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function getList($clientId=false)
    {
        $user = Util::getUserDetail();

        $clientId=($user->role =="admin")?$clientId:$user->id;
        $channel =intval(request()->channel);
        if(!$clientId)  return response()->json([
            'status' => false,
            'message' => 'Client Id Is Required'
        ], 422);

        try {
            $mediaList = $user->MediaList($channel)->select('id', 'url', 'des')->get();
            return response()->json([
                'status' => true,
                'message' => 'Media List', 'media_list' => $mediaList
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }
}
