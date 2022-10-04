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

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()]);
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
            $file->storeAs("client/media/$fid",$data['url']);
        }

        try {
            $media = $client->media()->create($data);

            return response()->json(['status' => false, 'message' => 'Media Added!']);
        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }


    public function getFileByUrl($file){
        $fid =hex2bin(substr($file,0,strpos($file,'_')));
        $ClientIdCheck =str_replace('f','',$fid);
        
        $filePath = storage_path("app/client/media/$fid/$file");
        return response()->download($filePath,str_replace('_','',$file));
        return $fid;
    }

    public function fetchAllMedia($clientId=false){
        $user = Util::getUserDetail();

    $clientId =($user->role =="client")?$user->id:$clientId;

 $checkValid = Validator::make(['id'=>$clientId],['id'=>'required|integer|exists:clients,id']);
 if($checkValid->fails()) return response()->json(['status'=>false,'message'=>'ID is Not Valid']);

 $client = Client::find($clientId);
 $clientMedia = $client->media()->get();
 return $clientMedia;


    }

}
