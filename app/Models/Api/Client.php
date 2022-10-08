<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

use Illuminate\Database\Eloquent\Model;

class Client extends Authenticatable
{

    //protected $withCount = ['comments','likes'];
protected $fillable = [
        'name',
        'email',
        'password','picture'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        "email_verified_at",
        "password",
        "remember_token"
    ];
    use HasApiTokens,HasFactory;

    public function clientProfile(){

        return $this->hasOne(\App\Models\ClientProfile::class);
    }

    public function media(){

        return $this->hasMany(\App\Models\ClientMedia::class);
    }

    public function likes(){

        return $this->hasMany(\App\Models\MediaLike::class);

    }

    public function comments(){

        return $this->hasMany(\App\Models\MediaComments::class);

    }
public function currentStatus(){
if(strchr(request()->path(),"api/user"))
{
return ['likes'=>$this->likes()->count(),
        'comments'=>$this->comments()->count()];
}else{

    return null;
}

} 


public function followers(){

 return $this->belongsToMany(\App\Models\Follower::class);

}

}
