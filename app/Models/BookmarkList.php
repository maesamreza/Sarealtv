<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookmarkList extends Model
{
    //use HasFactory;

    protected $fillable=['client_id','title','des','type'];

    public function getListDetails(){

        return $this->hasOne(MediaBookmark::class)->selectRaw("CASE WHEN client_media.thumbs=NULL THEN admin_media.thumbs ELSE client_media.thumbs END AS thumbs")
        ->leftJoin('client_media','media_bookmarks.client_media_id','client_media.id')
        ->leftJoin('admin_media','media_bookmarks.admin_media_id','admin_media.id');
       }
}
