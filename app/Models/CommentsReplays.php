<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentsReplays extends Model
{
    use HasFactory;

    protected $fillable =['client_id','comments'];

    protected $hidden = [ 
        'created_at',
        'updated_at'
    ];
}
