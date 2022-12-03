<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminComments extends Model
{
    protected $with = ['commentsReplays'];

    protected $hidden = [
       
        'created_at',
        'updated_at',
    ];
    use HasFactory;
    protected $fillable =['admin_media_id','client_id','comments'];

    public function commentsReplays(){
        return $this->hasMany(\App\Models\Admin\AdminCommentsReplay::class)
        ->select('admin_comments_replays.*','clients.name as replay_of',
        'client_profiles.picture','client_profiles.gender','client_profiles.account_type')
        
        ->join('client_profiles','admin_comments_replays.client_id','client_profiles.client_id')
        ->selectRaw('DATE_FORMAT(admin_comments_replays.updated_at, "%d %b %y") as date')
        ->join('clients', 'admin_comments_replays.client_id', 'clients.id');
    }
}
