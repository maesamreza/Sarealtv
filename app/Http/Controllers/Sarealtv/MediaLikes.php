<?php
namespace App\Http\Controllers\Sarealtv;

use App\Models\ClientMedia as Media;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Api\Tools\Util;

class MediaLikes extends Controller
{

public function like(Request $req,$mediaId){

    $user = Util::getUserDetail();
    $rules = [
              'id'=>'required|integer|exists:client_media,id'];
    $checkInputs = Validator::make(['id'=>$mediaId],$rules);
    if($checkInputs->fails()) return response()->json(['status'=>false,
                'message'=>'Inputs Not Valid!','errors'=>$checkInputs->errors()],422);
   
   try{
                $media = Media::find($mediaId);
    if($media->likes()->where('client_id',$user->id)->delete()){
        return response()->json(['status'=>true,
        'message'=>'Media Dislike Recorded!']);
       }
       else if($media->likes()->create(['client_id'=>$user->id,
       'likes'=>1])){

        return response()->json(['status'=>true,
        'message'=>'Media like Recorded!']);
       }
    }
    catch (\illuminate\Database\QueryException $e) {
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }
}


public function getLikes($mediaId){   
   // $user = Util::getUserDetail();
    $rules = [
              'id'=>'required|integer|exists:client_media,id'];
    $checkInputs = Validator::make(['id'=>$mediaId],$rules);
    if($checkInputs->fails()) return response()->json(['status'=>false,
                'message'=>'Inputs Not Valid!','errors'=>$checkInputs->errors()],422);
   
   try{
                $likesCount = \App\Models\MediaLike::where('client_media_id',$mediaId)
                ->select('media_likes.id as id','media_likes.client_id as client_id','name')
                ->join('clients', 'media_likes.client_id', '=', 'clients.id')
                ->get();
        return response()->json(['status'=>true,
        'message'=>'Media likes Total','total_likes'=>$likesCount]);
       }

    catch (\illuminate\Database\QueryException $e) {
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }


}



}
