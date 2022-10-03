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
}
