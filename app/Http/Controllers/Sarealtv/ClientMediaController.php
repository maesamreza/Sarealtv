<?php

namespace App\Http\Controllers\Sarealtv;


use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use App\Models\ClientMedia as Media;
use App\Http\Controllers\Api\Tools\Util;
use App\Models\Api\Client;
use stdClass;

class ClientMediaController extends Controller
{
    public $user;

    public function __construct()
    {

        //
    }

    public function addMedia(Request $req, $clientId = false)
    {

        $this->user = Util::getUserDetail();

        $client = ($this->user->role == 'admin' && $clientId) ?
            Client::find($clientId) :
             $user = ($this->user->role == 'admin')? $this->user:Client::find($this->user->id);

            if ($this->user->role != 'admin' && $client->media()->count()>3) {
                return response()->json(['status' => false, 'message' => 'Only 4 Media Files Can Be Added!']);
            }
        $maxSize =($this->user->role == 'admin')?"":"|max:21024";
        $maxDur =($this->user->role == 'admin')?"":"|max:30";
        $rule = [
            'title' => 'required|string',
            'des' => 'nullable|string',
            'media' => 'required|mimes:jpeg,jpg,gif,png',
            'thumbs' => 'nullable|mimes:jpeg,jpg,gif,png',
        ];
        $data = [
            'title' => $req->title,
            'des' => $req->des,
             'subDes'=>$req->subDes
        ];

        if (
            $req->hasFile('media') &&
            in_array($req->file('media')->extension(), ['mp4', '3gp', 'mov', 'avi', 'webm'])
        ) {
            $rule['media'] = $rule['media'] . ",webm,mp4,3gp,mov,avi $maxSize";
            $rule['duration'] = "required|integer $maxDur";
            $data['type'] = 'video';
            $data['duration'] = $req->duration;
        } else {
            $rule['media'] = $rule['media'].$maxSize;
        }


        $checkValid = Validator::make($req->all(), $rule);

        if ($checkValid->fails()) {

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()],422);
        }

        if ($req->hasFile('media')) {

            $file = $req->file('media');
            $type = $file->extension();
            $fid="f".$this->user->id;
            $data['url'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['url']);
            $data['url']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['url'];
        }

        if ($req->hasFile('thumbs')) {

            $file = $req->file('thumbs');
            $type = $file->extension();
            $fid="f".$this->user->id;
            $data['thumbs'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['thumbs']);
            $data['thumbs']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['thumbs'];
        }

        try {
            $media = $client->media()->create($data);

            return response()->json(['status' =>true, 'message' => 'Media Added!']);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }


    public function getFileByUrl($file){
        $fid =hex2bin(substr($file,0,strpos($file,'_')));
        $ClientIdCheck =str_replace('f','',$fid);
        
        $filePath = storage_path("app/client/media/$file");
        return response()->file($filePath);
        // return response()->download($filePath,str_replace('_','',$file));
        // return $fid;
    }

    public function fetchAllMedia($clientId=false){
     $user = Util::getUserDetail();

      $clientId =(!$clientId && $user != null || $user != null && $user->role =="client" && !$clientId)?$user->id:$clientId; 
      $inputs =['id'=>$clientId];
      $rules =['id'=>'required|integer|exists:clients,id'];
      $adminMedia =false;

      $checkValid = Validator::make($inputs,$rules);
      if($checkValid->fails())
      {
        
        if($user && $user->role =="admin"){
            $checkValid = Validator::make($inputs,['id'=>'required|integer|exists:users,id']);
          
            if($checkValid->fails()){
    
                return response()->json(['status'=>false,'message'=>'ID is Not Valid']);
          
               } 
           else{
               $adminMedia =true;
    
            }
              }
        
        
        
       if(!$adminMedia)
        return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Log In']);
      
    
    
      }
      


      $client = ($adminMedia)?\App\Models\User::find($clientId):Client::with('clientProfile')->find($clientId);
      if($user && $user->role !="admin"){
      $wner =',"'.$client->name.'" as name,';
      $wner .='"'.$client->clientProfile->picture.'" as picture,';
      $wner .='"'.$client->clientProfile->gender.'" as gender,';
      $wner .='"'.$client->clientProfile->account_type.'" as account_type';
      }
      else{

        $wner=null;
      }
      $clientMedia = $client->media()
 ->selectRaw('DATE_FORMAT(client_media.updated_at, "%d %b %y") as date'.$wner)
 ->get();

 
   return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);

    }


    public function fetchAllMediaLiked($ownerId,$clientId=false){
        $user = Util::getUserDetail();
        
         $clientId =(!$clientId && $user != null || $user != null && $user->role =="client" && !$clientId)?$user->id:$clientId;
         $inputs =['id'=>$clientId];
         $rules =['id'=>'required|integer|exists:clients,id'];
         if($ownerId !="all"){
            $inputs['ownerId']=intval($ownerId);
            $rules['ownerId']='required|integer|exists:media_like,client_id';
         }else{
            $ownerId=false; 
         }
         $checkValid = Validator::make($inputs,$rules);
         if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Log In','errors'=>$checkValid->errors()]);
         $client = (!$clientId)?$user:Client::/*with('clientProfile')->*/find($clientId);
        //  $wner =',"'.$client->name.'" as name,';
        //  $wner .='"'.$client->clientProfile->picture.'" as picture,';
        //  $wner .='"'.$client->clientProfile->gender.'" as gender,';
        //  $wner .='"'.$client->clientProfile->account_type.'" as account_type';
         $clientMedia = $client->likeMedia($ownerId)
         ->selectRaw('DATE_FORMAT(client_media.updated_at, "%d %b %y") as date'/*.$wner*/)
         ->get();
        return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);
            }


            public function fetchAllMediaILike($ownerId,$clientId=false){
                $user = Util::getUserDetail();
                
                 $clientId =(!$clientId && $user != null || $user != null && $user->role =="client" && !$clientId)?$user->id:$clientId; 
                 $inputs =['id'=>$clientId];
                 $rules =['id'=>'required|integer|exists:clients,id'];
                 if($ownerId !="all"){
        
                    $inputs['ownerId']=$ownerId;
                    $rules['ownerId']='required|integer|exists:media_like,owner_id';
                 }else{
                    $ownerId=false; 
                 }
                 $checkValid = Validator::make($inputs,$rules);
                 if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Log In']);
                 $client = (!$clientId)?$user:Client::/*with('clientProfile')->*/find($clientId);
                //  $wner =',"'.$client->name.'" as name,';
                //  $wner .='"'.$client->clientProfile->picture.'" as picture,';
                //  $wner .='"'.$client->clientProfile->gender.'" as gender,';
                //  $wner .='"'.$client->clientProfile->account_type.'" as account_type';
                 $clientMedia = $client->ilikeMedia($ownerId)
                 ->selectRaw('DATE_FORMAT(client_media.updated_at, "%d %b %y") as date'/*.$wner*/)
                 ->get();
                 return response()->json(['status'=>true,'message'=>$clientMedia->count().' media found','media'=>$clientMedia]);
                    }


                    
            public function getMediaById($MediaId){
                  //$user = Util::getUserDetail();
                
                 $checkValid = Validator::make(['id'=>$MediaId],['id'=>'required|integer|exists:client_media,id']);
                 if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Public']);
            
                 $clientMedia = Media::with('clientInfo')
                 ->selectRaw('DATE_FORMAT(client_media.updated_at, "%d %b %y") as date')
                 ->where('id',$MediaId)->first();
                 return response()->json(['status'=>true,'message'=>'Media Details','media'=>$clientMedia]);
         
                    }


}
