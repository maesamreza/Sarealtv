<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Tools\Util;
use App\Models\Api\Client;
use Validator;
use DB;
use Carbon\Carbon;
use Mail;
use Hash;
use File;
use App\Models\User;

class AdminController extends Controller

{
    public function login(Request $req)
    {

        $messages = array(
            "email.exists" => "EmailNotValid",
        );
        $valid = Validator::make($req->all(), ['email' => 'required|email|exists:users,email', 'password' => 'required|string|min:8'], $messages);

        if ($valid->fails()) {

            $errors = $valid->errors();
            if ($errors->has('email') && $errors->first('email') == "EmailNotValid") {

                return response()->json(['status' => false, 'message' => 'Credentials Errors'], 500);
            }

            return response()->json(['status' => false, 'message' => 'Input Validation Errors', "errors" => $errors]);
        }


        if (auth()->guard('admin')->attempt(['email' => $req->email, 'password' => $req->password]) && $token = auth()->guard('admin')->user()->createToken('uu4f3b5e03853b', ['admin'])->accessToken) {
            $user = auth()->guard('admin')->user();
            return response()->json(['status' => true, 'message' => 'Login successfully', 'accessToken' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'role' => 'admin', 'email' => $user->email]], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Credentials Errors'], 500);
        }
    }



    public function updateProfile(Request $req)
    {

        $user = Util::getUserDetail();
        $userUpdate = User::find($user->id);

        $rules = [
            'name' => 'nullable|string|min:4',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024'
        ];

        if ($req->has('email') && $req->email != $userUpdate->email) $rules['email'] = 'required|email|unique:users,email';

        if ($req->has('password')) {
            $rules = array_merge($rules, [
                'password' => 'required|string|min:8',
                'passwordConfirmation' => 'required|same:password'
            ]);
        }


        $valid = Validator::make($req->all(), $rules);

        if ($valid->fails()) {

            return response()->json(['status' => false, 'message' => 'Input Validation Errors', "inputErrors" => $valid->errors()], 500);
        }

        $data = [];
        if ($req->has('name'))  $data['name'] = $req->name;
        if ($req->has('email'))  $data['email'] = $req->email;
        if ($req->has('password')) $data['password'] = Hash::make($req->password);

        if ($req->picture != null) {


            if ($user != null && $user->picture != null) {

                $path = str_replace($req->getSchemeAndHttpHost(), '', $user->picture);

                if (File::exists(public_path($path))) {

                    File::delete(public_path($path));
                }
            }

            $name = uniqid() . "." . $req->picture->extension();
            if ($req->picture->move(public_path('admin/docs/images'), $name)) {
                $data['picture'] = url('admin/docs/images', $name);
            }
        }



        if ($user->tokenCan('admin') && $userUpdate->update($data)) {

            return response()->json(['status' => true, 'message' => 'Your profile Updated successfully'], 200);
        } else {

            return response()->json(['status' => false, 'message' => 'Server Error Can\'t Update Your profile'], 500);
        }
    }



    public function forgetPassword(Request $request)
    {

        $type = ['admin' => 'users', 'client' => 'clients'][request()->type];
        $valid = Validator::make($request->all(), [
            'email' => 'required|email|exists:' . $type,
        ]);
        if ($valid->fails())
            return response(['message' => 'Validation Error', 'error' => $valid->errors()->all()]);

        $token = rand(100000, 999999);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('email.forgetPassword', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            //$message->to('iirfanumer@gmail.com');
            $message->subject('Reset Password');
        });

        return response(['info' => 'We have e-mailed your password reset Token!'], 200);
    }






    public function resetPassword(Request $request)
    {

        $type = ['admin' => 'users', 'client' => 'clients'][request()->type];

        $valid = Validator::make($request->all(), [
            'token' => 'required|string|exists:password_resets,token',
            'email' => 'required|email|exists:' . $type,
            'password' => 'required|string|min:8',
            'passwordconfirmation' => 'required|same:password'
        ]);

        if ($valid->fails())
            return response(['message' => 'Validation Error', 'error' => $valid->errors()->all()]);


        $updatePassword = DB::table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if (!$updatePassword) {
            return response(['error' => 'Invalid token!']);
        }

        $user =  self::checkModelForReset($type, $request->email);
        if ($user->update(['password' => Hash::make($request->password)]) && DB::table('password_resets')->where(['email' => $request->email])->delete()) {

            return response(['status' => true, 'message' => 'Your password has been changed!'], 200);
        } else {
            return response(['satus' => false, 'message' => 'Unable to reset Password!'], 500);
        }
    }



    public function emailVerify(Request $request)
    {

        $type = ['verify' => 'clients'][request()->type];

        $valid = Validator::make($request->all(), [
            'token' => 'required|string|exists:password_resets,token',
            'email' => 'required|email|exists:' . $type,
        ]);

        if ($valid->fails())
            return response(['message' => 'Validation Error', 'error' => $valid->errors()->all()]);


        $updatePassword = DB::table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if (!$updatePassword) {
            return response(['error' => 'Invalid token!']);
        }

        $user = \App\Models\Api\Client::where('email',$request->email);
       try{
         if ($user->update(['email_verified_at' => now()]) && DB::table('password_resets')->where(['email' => $request->email])->delete()) {
            if (auth()->guard('admin')->attempt(['email' =>$request->email]) && $token = auth()->guard('admin')->user()->createToken('uu4f3b5e03853b', ['admin'])->accessToken) {
                $user = auth()->guard('admin')->user();
                return response()->json(['status' => true, 'message' => 'Email Verified Successfully!', 'accessToken' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'role' => 'admin', 'email' => $user->email]], 200);
                } 
            return response(['status' => true, 'message' => 'email Verified!'], 200);
           }
    }
    catch(\Throwable $th){


    } 
    }


    static function verifyEmail($email){

       try{
        $token = rand(100000, 999999);

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('email.forgetPassword', ['token' => $token], function ($message) use ($email) {
            $message->to($email);
            //$message->to('iirfanumer@gmail.com');
            $message->subject('Email Verification');
        });
    
    return true;}
    catch(\Throwable $th){
        return $th;
        return false;
    }

    }


    static function checkModelForReset($hint, $email)
    {

        $user = null;
        switch ($hint) {

            case "users":
                $user = \App\Models\User::where('email', $email);
                break;
            case "clients":
                $user = \App\Models\Client::where('email', $email);
                break;
        }

        return $user;
    }


    public function getMyDetails()
    {$user= Util::getUserDetail();

        return ($user->role =="admin")?$user:$user->load('clientProfile');
    }


    public function getMyDetailsById($id)
    {

        $user= Util::getUserDetail();
    
        $checkValid = Validator::make(['id' => $id], ['id' => 'required|integer|exists:clients,id']);
        if ($checkValid->fails())
            return response(['status' => false, 'message' => 'Profile ID is Not Valid ']);

        $clientData = ($user->role =="admin")? $user:Client::find($id)->load('clientProfile');
        $clientData->CurrentStatus = $clientData->currentStatus();
        if($user !=null){
           $clientData->is_following = $clientData->followers()->where('id',$user->id)->exists();
           $clientData->is_follower = $clientData->following()->where('id',$user->id)->exists();
        }
        return response([
            'status' => true,
            'message' => $clientData->name . " Profile Details and Media",
            'user' => $clientData
        ]);
    }

    public function findAccountByKey($searchKey)
    {
        $checkValid = Validator::make(['search' => $searchKey], ['search' => 'required|string|min:2']);
        if ($checkValid->fails())
            return response(['status' => false, 'message' => 'Search Key is Not Valid ']);
try{
        $clientData = Client::where('name','LIKE',"%%$searchKey%%")
        ->select('clients.id','clients.name','client_profiles.picture','client_profiles.gender','client_profiles.account_type')
        ->join('client_profiles','clients.id','client_profiles.client_id')->get();
        $total =count($clientData);
        $t =($total>0)?$total:"No";
        return response()->json([
            'status' => ($total>0),
            'message' => "$t Matches Found",
            'result'=>$clientData
        ]);
    
    }
    catch (\illuminate\Database\QueryException $e) {
    
        return response()->json(['status' => false, 'message' => $e->errorInfo[2]]);
    }


    }
}
