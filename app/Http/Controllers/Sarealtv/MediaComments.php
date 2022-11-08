<?php
namespace App\Http\Controllers\Sarealtv;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Models\ClientMedia as Media;
use App\Http\Controllers\Api\Tools\Util;
use App\Models\MediaComments as Comment;

class MediaComments extends Controller
{


    public function addComment(Request $req,$mediaId){

        $user = Util::getUserDetail();
        $rules = ['comments'=>'required|string|min:1',
                  'id'=>'required|integer|exists:client_media,id'];
        $checkInputs = Validator::make(array_merge($req->all(),['id'=>$mediaId]),$rules);
        if($checkInputs->fails()) return response()->json(['status'=>false,
                    'message'=>'Inputs Not Valid!','errors'=>$checkInputs->errors()],422);
      
      try{              
        
        $media = Media::find($mediaId);
       $media->comments()->create(['client_id'=>$user->id,
           'comments'=>$req->comments]);
    
            return response()->json(['status'=>true,
            'message'=>'Media Comments Recorded!','refresh'=>$media->comments()->get()]);
           }
           catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    
    
    }


    public function removeComment($id){

        $user = Util::getUserDetail();
        $rules = ['id'=>'required|integer|exists:media_comments,id,client_id,'.$user->id];
        $rules2 = ['id'=>'required|integer|exists:media_comments,id','clientID'=>'required|integer|exists:client_media,client_id'];

        $checkInputs = Validator::make(['id'=>$id],$rules);
        if(($checkInputs->fails()) ? Validator::make(['id'=>$id,'clientID'=>$user->id],$rules2)->fails():false)
        { return response()->json(['status'=>false,
                    'message'=>'Param ID Not Valid!'],422);
        }

        try{
        $media = Comment::where('id',$id)->delete();
       
    
            return response()->json(['status'=>true,
            'message'=>'Media Comments Deleted!']);
           }
           catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    
    
    }


    public function fetchComments($mediaId){

        $user = Util::getUserDetail();
        $rules = ['id'=>'required|integer|exists:media_comments,client_media_id'];
       
        $checkInputs = Validator::make(['id'=>$mediaId],$rules);
        
        if($checkInputs->fails())
        { return response()->json(['status'=>false,
                    'message'=>'Param ID Not Valid!  Or No comments found'],422);
        }

        try{

      $comments = Comment::select("media_comments.id",
      "media_comments.client_id",
      "client_media_id",
      "comments",'clients.name as comment_of',
      'client_profiles.picture','client_profiles.gender','client_profiles.account_type')
      ->selectRaw('DATE_FORMAT(media_comments.updated_at, "%d %b %y") as date')
      ->join('client_profiles','media_comments.client_id','client_profiles.client_id')
      ->join('clients', 'media_comments.client_id', 'clients.id')
     ->where('client_media_id',$mediaId)->get();

      return response()->json(['status'=>true,
      'message'=>'Comments List On this Media','comments'=>$comments]);

        }
        catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }


    }
    

}
