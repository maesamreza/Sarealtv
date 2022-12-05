<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMediaLike extends Model
{
    use HasFactory;
    protected $hidden = [
       
        'created_at',
        'updated_at'
    ];
    protected $fillable =['client_media_id','client_id','owner_id'];
}
