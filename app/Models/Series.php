<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $fillable =['title','subDes','des','media_type_id','admin_media_category_id'];
    use HasFactory;
}
