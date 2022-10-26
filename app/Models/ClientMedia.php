<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Controllers\Api\Tools\Util;


class ClientMedia extends Model
{

    use HasFactory;
    public $visitor=0;
    protected $withCount = ['comments','likes'];

    protected $fillable = [
        'title',
        'des',
        'url',
        'duration',
        'type'
    ];

    
    protected $hidden = [
       
        'created_at',
        'updated_at'
    ];
    protected $appends = [
        'is_like'];

        function __construct() {
            $this->visitor=Util::getUserDetail()->id??0;
          }

    public function likes(){
        return $this->hasMany(MediaLike::class);
    }

    public function comments(){
        return $this->hasMany(MediaComments::class);
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
   
}
