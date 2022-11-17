<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $fillable =['message','is_deleted','reciever_id'];
    protected $hidden =["pivot"];

    public function messageDetails(){
     return $this->hasOne(\App\Models\MessageBridge::class);
    }
}
