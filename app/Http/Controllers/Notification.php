<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Tools\Util;
use Validator;
class Notification extends Controller
{


static function createNoti($reqData){

/*$rules =['title'=>'required|string','message'=>'request|string'];

 $checkData=Validator::make($reqData,$rules);

 if($checkData->fails()){

    return response()->json(['status'=>false,'message'=>'Required Params Missings','errors'=>$checkData->errors()]);
  
 }*/

 try{

    \App\Models\Notification::create($reqData);
    return response()->json(['status'=>true,'message'=>'Notification Sent!']);
  
 }
 catch(\Illuminate\Database\QueryException $e){

    return response()->json(['status'=>false,'message'=>'Fails to send Notification']);
  

 }

}


public function allRemoveNoti(){
       
    $user = Util::getUserDetail();

     try{
    
        if(!$user->Notifications()->delete()){
    
            return response()->json(['status'=>false,'message'=>'Fails to Remove Notifications']);
          
        
         }
        return response()->json(['status'=>true,'message'=>'All Notifications Removed!']);
      
     }
     catch(\Illuminate\Database\QueryException $e){
    
        return response()->json(['status'=>false,'message'=>'Fails to Remove Notifications']);
      
    
     }
 }

     public function RemoveNoti($id){
       
        $user = Util::getUserDetail();
    
         try{
        
            if(!$user->Notifications()->where('id',$id)->delete()){
                return response()->json(['status'=>false,'message'=>'Fails to Remove Notification']);
           
            }
            return response()->json(['status'=>true,'message'=>'Notification Removed!']);
          
         }
         catch(\Illuminate\Database\QueryException $e){
        
            return response()->json(['status'=>false,'message'=>'Fails to Remove Notification']);
          
        
         }
        
        }





        public function allNoti(){
       
            $user = Util::getUserDetail();
        
             try{
            
                $data = $user->Notifications()->get();
                return response()->json(['status'=>true,'message'=>'Notifications' ,'notifications'=>$data]);
              
             }
             catch(\Illuminate\Database\QueryException $e){
            
                return response()->json(['status'=>false,'message'=>'Fails to fetch Notifications']);
               }
         }
        


    }




