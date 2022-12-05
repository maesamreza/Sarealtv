<?php
namespace App\Http\Controllers\Api;

use App\Models\AdminMedia as Media;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Api\Tools\Util;

class AdminMediaLikes extends Controller
{

public function like(Request $req,$mediaId){

    $user = Util::getUserDetail();
    $rules = [
              'id'=>'required|integer|exists:admin_media,id'];
    $checkInputs = Validator::make(['id'=>$mediaId],$rules);
    if($checkInputs->fails()) return response()->json(['status'=>false,
                'message'=>'Inputs Not Valid!','errors'=>$checkInputs->errors()],422);
   
   try{
        $media = Media::select('id','user_id')->find($mediaId);
    
    if($media->likes()->where('client_id',$user->id)->delete()){
        return response()->json(['status'=>true,
        'message'=>'Like Removed!','refresh'=>$media->likes()->count()]);
       }
       else if($media->likes()->create(['client_id'=>$user->id,
       'owner_id'=>$media->user_id])){

        return response()->json(['status'=>true,
        'message'=>'Like Added!','refresh'=>$media->likes()->count()]);
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
                $likesCount = \App\Models\Admin\AdminMediaLike::where('admin_media_id',$mediaId)
                ->select('admin_media_likes.admin_media_id as media_id','admin_media_likes.client_id as client_id','name')
                ->join('clients', 'admin_media_likes.client_id', '=', 'clients.id')
                ->get();
        return response()->json(['status'=>true,
        'message'=>'Media likes Total','total_likes'=>$likesCount]);
       }

    catch (\illuminate\Database\QueryException $e) {
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }


}



}
