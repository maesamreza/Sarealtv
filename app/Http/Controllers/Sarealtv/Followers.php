<?php

namespace App\Http\Controllers\Sarealtv;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Api\Tools\Util;
use App\Http\Controllers\Controller;

class Followers extends Controller
{
    

    public function follow($clientId){
       
        $user = Util::getUserDetail();
        $rules = ['id'=>'required|integer|exists:clients,id'];
       
        $checkInputs = Validator::make(['id'=>$clientId],$rules);
        
        if($checkInputs->fails())
        { return response()->json(['status'=>false,
                    'message'=>'Param ID Not Valid!'],422);
        }

        try{
        
$user->followers()->attach($clientId);

return response()->json(['status' => true,
 'message' =>'Now Your Follower!']);
       

        }
        catch(\illuminate\Database\QueryException $e){
            $errorCode =$e->errorInfo[1];
            if($errorCode == 1062) 
              return response()->json(['status' => false, 'message' =>'Your Allready Follower!']);

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
       

        }


    }

}
