<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCommentsReplay extends Model
{

    protected $fillable =['client_id','comments'];
    use HasFactory;
}
