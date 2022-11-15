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



    public function fetchListNames($type,$clientId=false)
    {
        $user = Util::getUserDetail();

        $clientId=($user->role =="admin")?intval($clientId):$user->id;
        $channel =intval(request()->channel);
        if(!$clientId)  return response()->json([
            'status' => false,
            'message' => 'Client Id Is Required'
        ], 422);

        $rules = [
            'listId' => "required|string|in:private,public,all"
            
        ];
        ($type !='all')?$rules['checkType']="required|integer|exists:bookmark_lists,client_id,type,$type":
        $rules['checkType']="required|integer|exists:bookmark_lists,client_id";
        $checkInputs = Validator::make([
        'listId'=>$type,
        'checkType'=>$clientId], $rules);
        if ($checkInputs->fails()) return response()->json([
            'status' => false,
            'message' => 'No Result Found!..']);


        try {
            $ListNames = \App\Models\BookmarkList::select('id', 'title', 'des','type')
            ->where('client_id',$clientId);
    
            ($type !='all')?  $ListNames =$ListNames->where('type',$type)->get(): $ListNames =$ListNames->get();
            return response()->json([
                'status' => true,
                'message' => 'List Names', 'list_name' => $ListNames
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }

    public function AddToList($mediaId,$listId)
    {

        $user = Util::getUserDetail();

        $rules = [
            'listId' => "required|integer|exists:bookmark_lists,id,client_id,$user->id",
            'id' => 'required|integer|exists:client_media,id',
            'checkList' => ["required",'integer',Rule::unique('media_bookmarks','client_media_id')
            ->where('client_id',$user->id)->where('bookmark_list_id',$listId)]
        ];
        $checkInputs = Validator::make(['id' => $mediaId,
        'checkList' =>$mediaId,
        'listId'=>$listId], $rules, [
            'checkList.unique' => 'This Media Allready In List'
        ]);
        if ($checkInputs->fails()) return response()->json([
            'status' => false,
            'message' => 'Inputs Not Valid!', 'errors' => $checkInputs->errors()
        ], 422);
        try {

            $media = \App\Models\ClientMedia::select('id', 'client_id')->find($mediaId);
            $media->MediaList()->create(['owner_id' => $media->client_id,
             'client_id' => $user->id,
            'bookmark_list_id'=>$listId]);
            return response()->json([
                'status' => true,
                'message' => 'Added to List'
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }

    public function RemoveFromList($mediaId,$listId, $clientId = false)
    {

        $user = Util::getUserDetail();
        $clientId=($user->role =='admin')?intval($clientId):$user->id;
        if(!$clientId)  return response()->json([
            'status' => false,
            'message' => 'Client Id Is Required'
        ], 422);

        $rules = [
            'id' => "required|integer|exists:media_bookmarks,client_media_id,client_id,$clientId,bookmark_list_id,$listId"
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



    public function getList($listId,$clientId=false)
    {
        $user = Util::getUserDetail();

        $clientId=($user->role =="admin")?intval($clientId):$user->id;
        $channel =intval(request()->channel);
        if(!$clientId)  return response()->json([
            'status' => false,
            'message' => 'Client Id Is Required'
        ], 422);
        $rules = [
            'listId' => "required|integer|exists:bookmark_lists,id,client_id,$clientId"
        ];
        $checkInputs = Validator::make([
        'listId'=>$listId], $rules);
        if ($checkInputs->fails()) return response()->json([
            'status' => false,
            'message' => 'Inputs Not Valid!', 'errors' => $checkInputs->errors()
        ], 422);


        try {
            $mediaList = $user->MediaList($listId,$channel)->select('id', 'url', 'des','title')->get();
            return response()->json([
                'status' => true,
                'message' => 'Media List', 'media_list' => $mediaList,
                'list_details'=>\App\Models\BookmarkList::select('id','title','des')->find($listId)
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }
}
