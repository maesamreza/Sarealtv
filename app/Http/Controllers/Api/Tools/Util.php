<?php

namespace App\Http\Controllers\Api\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Util
{



  static function getUserDetail()
  {


    $user = null;
    $guards = ['admin_details', 'client_details'];

    foreach ($guards as $guard) {
      $user = auth()->guard($guard)->user();
      $role = explode('_', $guard)[0];
      if ($user != null && $user->tokenCan($role)) {
        $user->role = $role;
        break;
         }
    }

    if ($user->role == 'client') {
      $user->CurrentStatus = [
          'likes' => 0,
          'followers' =>0,
          'media' =>0,
          'favorate' => 0,
          //'mediaBasePath'=>request()->getSchemeAndHttpHost().'/media/f'.$user->id
      ];
  }
    return $user;
  }
}
