<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaLike extends Model
{
    protected $table='media_like';
    protected $hidden = [
       
        'created_at',
        'updated_at'
    ];
    protected $fillable =['client_media_id','client_id','owner_id'];
    use HasFactory;
}
