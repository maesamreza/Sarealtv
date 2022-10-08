<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaLike extends Model
{

    protected $hidden = [
       
        'created_at',
        'updated_at'
    ];
    protected $fillable =['client_media_id','client_id','likes'];
    use HasFactory;
}
