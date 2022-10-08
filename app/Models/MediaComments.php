<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaComments extends Model
{
    protected $with = ['commentsReplays'];

    protected $hidden = [
       
        'created_at',
        'updated_at',
    ];
    use HasFactory;
    protected $fillable =['client_media_id','client_id','comments'];

    public function commentsReplays(){
        return $this->hasMany(CommentsReplays::class)
        ->select('comments_replays.*','clients.name as replay_of')
        ->join('clients', 'comments_replays.client_id', 'clients.id');
    }
}
