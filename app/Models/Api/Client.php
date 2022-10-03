<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

use Illuminate\Database\Eloquent\Model;

class Client extends Authenticatable
{

protected $fillable = [
        'name',
        'email',
        'password','picture'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    use HasApiTokens,HasFactory;

    public function clientProfile(){

        return $this->hasOne(\App\Models\ClientProfile::class);
    }

    public function media(){

        return $this->hasMany(\App\Models\ClientMedia::class);
    }
}
