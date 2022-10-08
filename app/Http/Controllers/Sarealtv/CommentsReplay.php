<?php
namespace App\Http\Controllers\Sarealtv;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Http\Controllers\Api\Tools\Util;
use App\Models\MediaComments as Comment;
use App\Models\CommentsReplays as Replay;

class CommentsReplay extends Controller
{


    public function addCommentReplay(Request $req,$commentId){

        $user = Util::getUserDetail();


        $rules = ['comments'=>'required|string|min:1',
                  'id'=>'required|integer|exists:media_comments,id'];


        $checkInputs = Validator::make(array_merge($req->all(),['id'=>$commentId]),$rules);


        if($checkInputs->fails()) return response()->json(['status'=>false,
                    'message'=>'Inputs Not Valid!','errors'=>$checkInputs->errors()],422);
      
      try{              
        
        $media = Comment::find($commentId);
       $media->commentsReplays()->create(['client_id'=>$user->id,
           'comments'=>$req->comments]);
    
            return response()->json(['status'=>true,
            'message'=>'Media Comment Replay Recorded!']);
           }
           catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    
    
    }


    public function removeCommentReplay($id){

        $user = Util::getUserDetail();
        $rules = ['id'=>'required|integer|exists:comments_replays,id,client_id,'.$user->id];
        $rules2 = ['id'=>'required|integer|exists:comments_replays,id',
        'clientID'=>'required|integer|exists:client_media,client_id'];

        $checkInputs = Validator::make(['id'=>$id],$rules);
        $checkInputs2= Validator::make(['id'=>$id,'clientID'=>$user->id],$rules2);
        if(($checkInputs->fails()) ? $checkInputs2->fails():false)
        { return response()->json(['status'=>false,
                    'message'=>'Param ID Not Valid! Or Your Unauthourized'],422);
        }

        try{
        $media = Replay::where('id',$id)->delete();
       
    
            return response()->json(['status'=>true,
            'message'=>'Media Comment Replay Deleted!']);
           }
           catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    
    
    }


    public function fetchCommentReplays($commentId){

        $user = Util::getUserDetail();
        $rules = ['id'=>'required|integer|exists:media_comments,id'];
       
        $checkInputs = Validator::make(['id'=>$commentId],$rules);
        
        if($checkInputs->fails())
        { return response()->json(['status'=>false,
                    'message'=>'Param ID Not Valid!'],422);
        }

        try{

      $comments = Replay::select("comments_replays.id",
      "client_id",
      "media_comments_id",
      "comments",'clients.name as replay_of')
      ->join('clients', 'comments_replays.client_id', 'clients.id')
      
      ->where('media_comments_id',$commentId)->get();

      return response()->json(['status'=>true,
      'message'=>'Comments List On this Media','comments'=>$comments]);

        }
        catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }


    }
    

}
