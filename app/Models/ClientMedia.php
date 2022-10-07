<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'des',
        'url',
        'duration',
        'type'
    ];

    public function likes(){
        return $this->hasMany(MediaLike::class);
    }

    public function comments(){
        return $this->hasMany(MediaComments::class);
    }
    
}
