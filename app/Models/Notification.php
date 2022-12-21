<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $fillable =[
        'title',
        'message',
        'client_id','admin_media_id','media_id','sender_id','media_category',
           ];
   // use HasFactory;
}
