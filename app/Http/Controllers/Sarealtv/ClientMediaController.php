<?php

namespace App\Http\Controllers\Sarealtv;


use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
//use App\Models\ClientMedia as Media;
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
            Client::find($this->user->id);

        $rule = [
            'title' => 'required|string',
            'des' => 'nullable|string',
            'media' => 'required|mimes:jpeg,jpg,gif,png',
        ];
        $data = [
            'title' => $req->title,
            'des' => $req->des,

        ];

        if (
            $req->hasFile('media') &&
            in_array($req->file('media')->extension(), ['mp4', '3gp', 'mov', 'avi', 'webm'])
        ) {
            $rule['media'] = $rule['media'] . ',webm,mp4,3gp,mov,avi|max:21024';
            $rule['duration'] = 'required|integer|max:30';
            $data['type'] = 'video';
            $data['duration'] = $req->duration;
        } else {
            $rule['media'] = $rule['media'] . '|max:1024';
        }


        $checkValid = Validator::make($req->all(), $rule);

        if ($checkValid->fails()) {

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()],422);
        }

        if ($req->hasFile('media')) {

            $file = $req->file('media');
            $type = $file->extension();

            // if (in_array($type, ['mp4', '3gp', 'mov', 'avi']) && !$req->duration) {
            //     $error = new stdClass;

            //     $error->duration = ['duration fields is required with video'];
            //     return $error;
            //     return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $error]);
            // }
           $fid="f".$this->user->id;
            $data['url'] = bin2hex($fid)."_".uniqid() . '_media' . time() . '.' . $type;
            $file->storeAs("public/media/",$data['url']);
            $data['url']= request()->getSchemeAndHttpHost().'/storage/media/'.$data['url'];
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

 $clientId =(!$clientId && $user != null || $user != null && $user->role =="client")?$user->id:$clientId;

 $checkValid = Validator::make(['id'=>$clientId],['id'=>'required|integer|exists:clients,id']);
 if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid Or Not Log In']);
 $client = Client::with('clientProfile')->find($clientId);
 $wner =',"'.$client->name.'" as name,';
 $wner .='"'.$client->clientProfile->picture.'" as picture,';
 $wner .='"'.$client->clientProfile->gender.'" as gender,';
 $wner .='"'.$client->clientProfile->account_type.'" as account_type';
 $clientMedia = $client->media()
 ->selectRaw('DATE_FORMAT(client_media.updated_at, "%d %b %y") as date'.$wner)
 ->get();
 return $clientMedia;
    }

}
