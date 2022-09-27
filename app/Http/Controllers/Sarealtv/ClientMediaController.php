<?php

namespace App\Http\Controllers\Sarealtv;


use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
//use App\Models\ClientMedia as Media;
use App\Http\Controllers\Api\Tools\Util;
use App\Models\Api\Client;

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
            'media' => 'required|mimes:jpeg,jpg,gif,png,mp4,3gp,mov,avi|max:1024'
        ];

        $checkValid = Validator::make($req->all(), $rule);

        if ($checkValid->fails()) {

            return response()->json(['status' => false, 'message' => 'Invalid Data', 'errors' => $checkValid->errors()]);
        }

        if ($req->hasFile('media')) {

            $file = $req->file('media');
            $type = $file->extension();
            $path = uniqid() . '_media'.time().'.'. $type;
        }

        try {
            $media = $client->media()->create([
                'title' => $req->title,
                'des' => $req->des,
                'url' => $path
            ]);

            return response()->json(['status' => false, 'message' => 'Media Added!']);

        } catch (\illuminate\Database\QueryException $e) {
            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }
}
