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
    protected $fillable =['client_media_id','client_id','owner_id','bookmark_list_id'];


    public function firstThumbs(){
        if($this->client_media_id) return $this->hasOne(AdminMedia::class,'id','admin_media_id');
        return $this->hasOne(ClientMedia::class,'id','client_media_id');
    }


}
