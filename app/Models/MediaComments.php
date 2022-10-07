<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaComments extends Model
{
    use HasFactory;
    protected $fillable =['client_media_id','client_id','comments'];

    public function commentsReplays(){
        return $this->hasMany(CommentsReplays::class);
    }
}
