<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $table ='clients';
    protected $hidden = [
        'created_at',
        'updated_at',
        "email_verified_at",
        "password",
        "remember_token","pivot",'email'
    ];
    use HasFactory;
}
