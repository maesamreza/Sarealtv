<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeriesSeason extends Model
{

    protected $fillable =[
        "title",
        "subDes",
        "des",
        "series_id",
        "season","thumbs"
    ];
   
    public function Eposode(){

        return $this->hasMany(\App\Models\SeriesMedia::class,'season');
    } 
}
