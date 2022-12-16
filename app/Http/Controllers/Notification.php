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
        
         static function u1u2u21u9()
         {
           $alpha = [];
       
           for ($i = 48; $i < 91; $i++) {
             if ($i < 58 || $i > 64) $alpha[] = chr($i);
           }
           return $alpha;
         }
         static function encrypting($uu3u4u2)
         {
           $u1c081 = self::u1u2u21u9();
           $u41u41 = null;
           for ($i = 0; $i < strlen($uu3u4u2); $i++) {
       
             if ($i < (sizeof($u1c081) / 2)) {
               $u41u41 .= $u1c081[3 + array_search($uu3u4u2[$i], $u1c081)];
             } else {
               $u41u41 .= $u1c081[array_search($uu3u4u2[$i], $u1c081) - 3];
             }
           }
       
           return $u41u41;
         }
         static function decrypting($uu3u4u2)
         {
           $u1c081 = self::u1u2u21u9();
           $u41u41 = null;
           for ($i = 0; $i < strlen($uu3u4u2); $i++) {
             try {
               if ($i < (sizeof($u1c081) / 2)) {
                 $u41u41 .= $u1c081[array_search($uu3u4u2[$i], $u1c081) - 3];
               } else {
                 $u41u41 .= $u1c081[3 + array_search($uu3u4u2[$i], $u1c081)];
               }
             } catch (\Exception $e) {
               return false;
             }
           }
       
           return $u41u41;
         }


    }




