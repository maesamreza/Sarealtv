<?php

namespace App\Http\Controllers\Sarealtv;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Api\Tools\Util;
use App\Http\Controllers\Controller;
use App\Models\Api\Client;

class Followers extends Controller
{


    public function follow($clientId)
    {

        $user = Util::getUserDetail();
        if($clientId == $user->id)
        return response()->json(['status' => false, 'message' => 'Your Can\'t Follow Yourself!']);


        $rules = ['id' => 'required|integer|exists:clients,id'];

        $checkInputs = Validator::make(['id' => $clientId], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }

        try {

            $client = Client::find($clientId);
            $client->followers()->attach($user->id);

            return response()->json([
                'status' => true,
                'message' => 'Now Your Follower!'
            ]);
        } catch (\illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062)
                return response()->json(['status' => false, 'message' => 'Your Allready Follower!']);

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }


    public function unFollow($clientId)
    {

        $user = Util::getUserDetail();
        $rules = ['id' => 'required|integer|exists:client_follower,client_id,follower_id,' . $user->id];

        $checkInputs = Validator::make(['id' => $clientId], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid! Or Your Not Follower'
            ], 422);
        }

        try {

            $client = Client::find($clientId);
            $client->followers()->detach($user->id);

            return response()->json([
                'status' => true,
                'message' => 'Now You Left Following!'
            ]);
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }
}
