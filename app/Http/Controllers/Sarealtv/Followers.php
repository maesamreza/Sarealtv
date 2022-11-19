<?php

namespace App\Http\Controllers\Sarealtv;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Api\Tools\Util;
use App\Http\Controllers\Controller;
use App\Models\Api\Client;


class Followers extends Controller
{


    public function follow($clientId, $followerId = false)
    {

        $user = Util::getUserDetail();
        if ($clientId == $user->id)
            return response()->json(['status' => false, 'message' => 'Your Can\'t Follow Yourself!']);


        $rules = ['id' => 'required|integer|exists:clients,id'];
        $inputs = ['id' => $clientId];
        if ($followerId) {
            $rules['follower_id'] = 'required|integer|exists:clients,id';
            $inputs['follower_id'] = $followerId;
        }
        $checkInputs = Validator::make($inputs, $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }
        try {
            if ($user->role == 'admin' && $followerId) $user = Client::find($followerId);
            $client = Client::find($clientId);
            $checkPrivate = ($client->clientProfile()->where('account_type', 'Private')->exists()
            && (!$client->id==$clientId || !$user->role=='admin'));
            if (!$checkPrivate) {
                $client->followers()->attach($user->id);

                return response()->json([
                    'status' => true,
                    'message' => "You've Followed $client->name!"
                ]);
            } else {
                $client->followerRequests()->attach($user->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Your Request For Following Sent!..'
                ]);
            }
        } catch (\illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062)
                return response()->json(['status' => true, 'message' => ($checkPrivate) ?
                    'Allready You Sent Request!..' : 'Your Allready Follower!']);

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
                'message' => "You've Unfollowed $client->name !"
            ]);
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }


    public function followers($clientId = false)
    {
        $user = Util::getUserDetail();

        $clientId = ($clientId) ? $clientId : $user->id;
        $rules = ['id' => 'required|integer|exists:clients,id'];

        $checkInputs = Validator::make(['id' => $clientId], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }


        try {

            if ($clientId) $user = Client::find($clientId);
            $followers = $user->followers()
                ->select('clients.*', 'client_profiles.picture', 'client_profiles.gender', 'client_profiles.account_type')
                ->join('client_profiles', 'clients.id', 'client_profiles.client_id')->get();
            return response()->json([
                'status' => true,
                'message' => 'List of Your Followers!',
                'followers' => $followers
            ]);
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function following($clientId = false)
    {
        $user = Util::getUserDetail();

        $clientId = ($clientId) ? $clientId : $user->id;

        $rules = ['id' => 'required|integer|exists:clients,id'];

        $checkInputs = Validator::make(['id' => $clientId], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }


        try {
            if ($clientId) $user = Client::find($clientId);
            $followers = $user->following()
                ->select('clients.*', 'client_profiles.picture', 'client_profiles.gender', 'client_profiles.account_type')
                ->join('client_profiles', 'clients.id', 'client_profiles.client_id')
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'List of You Following!',
                'following' => $followers
            ]);
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function followRequests($clientId = false)
    {
        $user = Util::getUserDetail();

        $clientId = ($clientId) ? $clientId : $user->id;
        $rules = ['id' => 'required|integer|exists:clients,id'];

        $checkInputs = Validator::make(['id' => $clientId], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }
        $followers = $user->followerRequests()->get();

        try {

            if ($clientId) $user = Client::find($clientId);
            $followers = $user->followerRequests()
                ->select('clients.*', 'client_profiles.picture', 'client_profiles.gender', 'client_profiles.account_type')
                ->join('client_profiles', 'clients.id', 'client_profiles.client_id')
                ->selectRaw('DATE_FORMAT(follow_requests.updated_at, "%d %b %y") as request_date, follow_requests.id as request_id')
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'List of Your Requests!',
                'Requests' => $followers
            ]);
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function followingRequests($clientId = false)
    {
        $user = Util::getUserDetail();

        $clientId = ($clientId) ? $clientId : $user->id;

        $rules = ['id' => 'required|integer|exists:clients,id'];

        $checkInputs = Validator::make(['id' => $clientId], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }


        try {
            if ($clientId) $user = Client::find($clientId);
            $followers = $user->followingRequests()
                ->select('clients.*', 'client_profiles.picture', 'client_profiles.gender', 'client_profiles.account_type')
                ->join('client_profiles', 'clients.id', 'client_profiles.client_id')
                ->selectRaw('DATE_FORMAT(follow_requests.updated_at, "%d %b %y") as request_date, follow_requests.id as request_id')
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'List of You Requests!',
                'Requests' => $followers
            ]);
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }


    public function acceptFollowRequest($id)
    {

        $user = Util::getUserDetail();
        $rules = [];

        $sufix = ($user->role == 'admin') ?: ",client_id,$user->id";
        $rules['id'] = 'required|integer|exists:follow_requests,id'.$sufix;

        $checkInputs = Validator::make(['id' => $id], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }

        try {
            $request = \App\Models\FollowRequest::find($id);
            $attampt =$this->follow($request->follower_id,$request->client_id);
            if($attampt->getData()->status)  $request->delete();
            return $attampt;
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



    public function deleteFollowRequest($id)
    {

        $user = Util::getUserDetail();
        $rules = [];

        $sufix = ($user->role == 'admin') ?: ",client_id,$user->id";
        $rules['id'] = 'required|integer|exists:follow_requests,id'.$sufix;


        $checkInputs = Validator::make(['id' => $id], $rules);

        if ($checkInputs->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Param ID Not Valid!'
            ], 422);
        }

        try {
            $request = \App\Models\FollowRequest::where('id',$id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'This Request Removed!..'
            ]);
           
        } catch (\illuminate\Database\QueryException $e) {

            return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
        }
    }



}
