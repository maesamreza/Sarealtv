<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaType extends Model
{
  
    protected $fillable =['name'];
    public $timestamps = false;
    public function categories(){
        return $this->hasMany(\App\Models\AdminMediaCategory::class)
        ->select('id','category','media_type_id');
    }
}
