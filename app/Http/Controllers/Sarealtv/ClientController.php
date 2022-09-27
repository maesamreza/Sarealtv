<?php

namespace App\Http\Controllers\Sarealtv;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use Hash;
use App\Models\Api\Client;
use App\Http\Controllers\Api\Tools\Util;
use File;

class ClientController extends Controller
{
    public function register(Request $req)
    {


        
        $validator = Validator::make($req->all(), [

            "email" => "required|unique:clients",
            // 'password' => 'required|confirmed|min:8',
            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'passwordConfirmation' => 'required|same:password',
            
            'firstName' => "required|string",
            'lastName'=>"required|string",
            'gender'=>'required|string|in:male,female',
            'country'=>'required|string',
            'DOB'=>'required|date',
            'accepted'=>'required|in:1',

        ], [
            'password.regex' => 'password should contain atleat one uppercase and lowercase characters and number'
        ]);
        if ($validator->fails()) {
            return response(['status' => false, 'errors' => $validator->errors()]);
        }

        $data = ['name' => $req->firstName.' '.$req->lastName, 'password' => Hash::make($req->password), 'email' => $req->email];
        $profile =[];
        $profile['DOB']=date('Y-m-d H:i:s' ,strtotime($req->DOB));
        $profile['country']=$req->country;

        $user = new Client();

        foreach ($data as $column => $value) {

            $user->$column = $value;
        }

        if ($user->save() && $user->clientProfile()->create($profile)) {

            if (auth()->guard('client')->attempt(['email' => $req->email, 'password' => $req->password]) && $token = auth()->guard('client')->user()->createToken('uu4f3b5e03853b', ['client'])->accessToken) {
                $user = auth()->guard('client')->user();
                return response()->json(['status' => true, 'message' => 'Your Registration Completed..', 'accessToken' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'role' => 'client', 'email' => $user->email]], 200);
            } else {
                return response()->json(['status' => true, 'message' => 'Your Registration Completed..'], 200);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Errors Occure During Registration..'], 500);
        }
    }







    public function login(Request $req)
    {
        $messages = array(
            "email.exists" => "EmailNotValid",
        );
        $valid = Validator::make($req->all(), ['email' => 'required|email|exists:clients,email', 'password' => 'required|string|min:8'], $messages);

        if ($valid->fails()) {

            $errors = $valid->errors();
            if ($errors->has('email') && $errors->first('email') == "EmailNotValid") {

                return response()->json(['status' => false, 'message' => 'Credentials Errors'], 200);
            }

            return response()->json(['status' => false, 'message' => 'Input Validation Errors', "errors" => $errors]);
        }


        if (auth()->guard('client')->attempt(['email' => $req->email, 'password' => $req->password]) && $token = auth()->guard('client')->user()->createToken('uu4f3b5e03853b', ['client'])->accessToken) {
            $user = auth()->guard('client')->user();
            return response()->json(['status' => true, 'message' => 'Login successfully', 'accessToken' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'role' => 'client', 'email' => $user->email]], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Credentials Errors'], 500);
        }
    }








    public function updateProfile(Request $req,$id=false)
    {
        $user = Util::getUserDetail();
        /*if($user->role =='admin' && $id) {
            $uid=$id;
        }
        else */
$uid = $id;
if ($user->role =='admin' && !$uid){
    return response()->json(['status' => false, 'message' => 'Client Id Is required']);
    
            }

        if ($user->role =='client'){
$uid = $user->id;
        }


        $rules =[
        
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',

            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'passwordConfirmation' => 'required|same:password',
            
            'firstName' => "required|string",
            'lastName'=>"required|string",
            'gender'=>'required|string|in:male,female',
            'DOB'=>'required|date',
            'country'=>'required|string',
            'accepted'=>'required|in:1',

        ];

        $rules = array_intersect_key($rules,$req->all());

        $rules['id']='required|integer|exists:clients,id';
       
        $userUpdate = Client::find($uid);

        if ($req->has('email') && $req->email != $userUpdate->email) $rules['email'] = 'required|email|unique:clients,email';

        if ($req->has('password')) {
            $rules = array_merge($rules, [
                'password' => [
                    'required',
                    'string',
                    'min:8',             // must be at least 10 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ],
                'passwordConfirmation' => 'required|same:password'

            ]);
        }


        $valid = Validator::make(array_merge($req->all(),['id'=>$uid]), $rules);

        if ($valid->fails()) {

            return response()->json(['status' => false, 'message' => 'Input Validation Errors', "inputErrors" => $valid->errors()], 500);
        }


        $data = [];
        if($req->has('firstName') && $req->has('lastName')) $data['name'] = $req->firstName.''.$req->lastName;
        if ($req->has('email'))  $data['email'] = $req->email;
        if ($req->has('password')) $data['password'] = Hash::make($req->password);
        $profile =[];
        if ($req->has('DOB')) $profile['DOB']=date('Y-m-d H:i:s' ,strtotime($req->DOB));
        if ($req->has('country')) $profile['country']=$req->country;
        if ($req->has('gender'))  $profile['gender'] = $req->gender;






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

        if ($userUpdate->update($data) && (sizeof($profile) && $userUpdate->clientProfile()->first()) ? $userUpdate->clientProfile()->update($profile):true) {

            return response()->json(['status' =>true, 'message' => 'Your profile Updated successfully'], 200);
        } else {

            return response()->json(['status' =>false, 'message' => 'Server Error Can\'t Update Your profile'], 500);
        }
    }
}
