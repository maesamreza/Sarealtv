<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Api\Tools\Util;
use Illuminate\Database\Eloquent\Casts\Attribute;


class AdminMedia extends Model
{


    protected $withCount = ['comments','likes'];
    public $visitor=0;

    protected $fillable = [
        'title',
        'des',
        'url',
        'duration',
        'type','subDes','thumbs'
    ];

    
    protected $hidden = [
       
        'created_at',
        'updated_at', "pivot"
    ];
    protected $appends = ['is_like'];

        function __construct() {
            $this->visitor=Util::getUserDetail()->id??0;
          }




      public function filterMedia($type=false,$cate=false,$season=false,$title=false){

     return $this->belongsToMany(self::class,'media_filter');
      
      }
      public function scopeSearch($query, $value)
    {

        if($value) return $query->where('title','LIKE',"%%$value%%");
    }   
    
    public function relatedMedia($series=false){

       // return self::query();


    }

    public function likes(){
        return $this->hasMany(\App\Models\Admin\AdminMediaLike::class);
    }

    public function comments(){
        return $this->hasMany(\App\Models\Admin\AdminComments::class);
    }

    
protected function isLike(): Attribute
{

    return Attribute::make(
        get: fn ($value, $attributes) =>($this->visitor || $this->client_id)?
        $this->likes()->where('client_id',$this->visitor)->exists():NULL,
        set: fn ($value) => [
            'is_like' =>$value,
        ],
    );
}

public function clientInfo(){

    return $this->hasOne(\App\Models\Api\Client::class,'id','client_id')
    ->select('clients.id','clients.name','client_profiles.picture','client_profiles.gender','client_profiles.account_type')
    ->join('client_profiles','clients.id','client_profiles.client_id');
}


public function MediaList()
{
    return $this->hasMany(\App\Models\MediaBookmark::class);
}
   
}
