<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Tools\Util;
use Validator;
use Illuminate\Validation\Rule;

class Messages extends Controller
{

    
    public function sendMessage(Request $req){

        $user = Util::getUserDetail();

        $rules =['message'=>'required|string|min:1',
    'reciever_id'=>'required|integer|exists:clients,id'];

        $checkFields=Validator::make($req->all(),$rules);
        if( $checkFields->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Inputs Not Valid!', 'errors' => $checkFields->errors()
            ], 422);
        }
      

        try{
            $message =new \App\Models\Message;
            $message->message=$req->message;
            $message->save();
            $user->Messages()->attach($message->id,[
            'reciever_id'=>$req->reciever_id]);
          return response()->json([
            'status' => true,
            'message' => 'Added to List'
        ]);
    } catch (\illuminate\Database\QueryException $e) {
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }
    }

    
    public function removeMessage($messageId){

        $user = Util::getUserDetail();

        $rules =['messageId'=>["required",'integer',Rule::exists('message_bridges','message_id')
        /*->where('reciever_id',$user->id)->orWhere('sender_id',$user->id)*/]];

        $checkFields=Validator::make(['messageId'=>$messageId],$rules);
        if( $checkFields->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Fails To Delete Message'], 422);
        }
     

        try{

            \App\Models\Message::where('id',$messageId)->whereHas('messageDetails',function($q) use($user){
          $q->where('reciever_id',$user->id)->orWhere('sender_id',$user->id);})->update(['is_deleted'=>1]);
          return response()->json([
            'status' => true,
            'message' => 'Message Deleted Successfully!'
        ]);
    } catch (\illuminate\Database\QueryException $e) {
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }
    }





    public function getMessageList($clientId){

        $user = Util::getUserDetail();

        $rules =['clientId'=>["required",'integer',Rule::exists('message_bridges','reciever_id')]];

        $checkFields=Validator::make(['clientId'=>$clientId],$rules);
        if( $checkFields->fails()){
            return response()->json([
                'status' => false,
                'message' => 'No Message history Found!..'], 422);
        }
     
        try{

         $messages = $user->Messages($clientId)->select('message_bridges.reciever_id','message_bridges.sender_id','message','messages.id','messages.created_at as date')
          //->whereIn('message_bridges.reciever_id',[$user->id,$clientId])
          //->orWhereIn('message_bridges.sender_id',[$user->id,$clientId])
          ->join('clients','message_bridges.reciever_id','clients.id')
          ->selectRaw("'$user->name' as sender,clients.name as reciever")
          ->get();
          return response()->json([
            'status' => true,
            'message' => 'Messages history!',
            'chating'=> $messages
        ]);
    } catch (\illuminate\Database\QueryException $e) {
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }
    }

    public function fetchChatings(){
        $user = Util::getUserDetail();

       return  $user->inboxList()->get();


    }
}
