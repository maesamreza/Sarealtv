<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MediaBookmarkController extends Controller
{



    public function AddToList($mediaId)
    {

        $user = Util::getUserDetail();

        $rules = [
            'id' => 'required|integer|exists:client_media,id',
            'checkList' => "required|integer|exists:media_bookmarks,!client_media_id,client_id,$user->id"
        ];
        $checkInputs = Validator::make(['id' => $mediaId], $rules, [
            'checkList.exists' => 'This Media Allready In List'
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

    public function RemoveFromList($mediaId,$clientId=false)
    {

        $user = Util::getUserDetail();
        $clientId=($user->role ='admin')?$clientId:$user->id;
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
  
            \App\Models\MediaBookmark::where('client_id',$clientId)->where('client_media_id',$mediaId)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Removed From List']);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function getList($clientId=false,$channel=false)
    {
        $user = Util::getUserDetail();

        $clientId=($user->role ='admin')?$clientId:$user->id;
        if(!$clientId)  return response()->json([
            'status' => false,
            'message' => 'Client Id Is Required'
        ], 422);
   
        try {
            $mediaList = \App\Models\ClientMedia::query()->select('id', 'url', 'des')
                ->join('media_bookmarks', 'client_media.id', '=', 'media_bookmarks.client_id')
                ->where('client_id',$clientId);

                ($channel)?$mediaList->where('owner_id',$channel)->get(): $mediaList->get();
            return response()->json([
                'status' => true,
                'message' => 'Media List', 'media_list' => $mediaList
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }
}
