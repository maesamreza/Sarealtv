<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMedia extends Model
{
    use HasFactory;
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
    public function likes(){
        return $this->hasMany(MediaLike::class);
    }

    public function comments(){
        return $this->hasMany(MediaComments::class);
    }
    
}
