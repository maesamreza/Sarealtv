<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaBookmark extends Model
{
    protected $hidden = [
       
        'created_at',
        'updated_at'
    ];
    protected $fillable =['client_media_id','client_id','owner_id'];
    //use HasFactory;
}
