<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

use Illuminate\Database\Eloquent\Model;

class Client extends Authenticatable
{

    //protected $withCount = ['comments','likes'];
    protected $fillable = [
        'name',
        'email','visits',
        'password', 'picture','is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        "email_verified_at",
        "password",
        "remember_token", "pivot"
    ];
    use HasApiTokens, HasFactory;

    public function clientProfile()
    {

        return $this->hasOne(\App\Models\ClientProfile::class);
    }

    public function media()
    {

        return $this->hasMany(\App\Models\ClientMedia::class);
    }

    public function likes()
    {

        return $this->hasMany(\App\Models\MediaLike::class);
    }

    public function comments()
    {

        return $this->hasMany(\App\Models\MediaComments::class);
    }
    public function currentStatus()
    {
        if (strchr(request()->path(), "api/user")) {
            return [
                'likes' => $this->likesOnMyMedia()->count(),
                'comments' => $this->comments()->count(),
                'followers' => $this->followers()->count(),
                'following' => $this->following()->count(),
                'Lists' => $this->ListNames('Public')->count()

            ];
        } else {

            return null;
        }
    }


    public function followers()
    {

        return $this->belongsToMany(\App\Models\Follower::class,);
    }

    public function following()
    {

        return $this->belongsToMany(
            \App\Models\Follower::class,
            'client_follower',
            'follower_id',
            'client_id'
        );
    }


    public function followerRequests()
    {

        return $this->belongsToMany(self::class, 'follow_requests', 'client_id', 'follower_id')->withTimestamps();
    }

    public function followingRequests()
    {

        return $this->belongsToMany(
            self::class,
            'follow_requests',
            'follower_id',
            'client_id'
        )->withTimestamps();
    }



    public function likesOnMyMedia()
    {

        return $this->hasMany(\App\Models\MediaLike::class, 'owner_id');
    }

    public function ilikeMedia($filterId = false)
    {

        $media =
            \App\Models\ClientMedia::query()->select('id', 'url', 'des')
            ->where('media_like.client_id', $this->id);
        if ($filterId) $media->where('media_like.owner_id', $filterId);

        return $media->join('media_like', 'client_media.id', 'media_like.client_media_id');
    }

    public function likeMedia($filterId = false)
    {

        $media =
            \App\Models\ClientMedia::query()->select('id', 'url', 'des')
            ->where('media_like.owner_id', $this->id);
        if ($filterId) $media->where('media_like.client_id', $filterId);

        return $media->join('media_like', 'client_media.id', 'media_like.client_media_id');
    }

    public function MediaList($listId,$id = false)
    {
        if ($id) return $this->belongsToMany(\App\Models\ClientMedia::class,'media_bookmarks', 'owner_id')
        ->where('bookmark_list_id',$listId)->withTimestamps();

        return $this->belongsToMany(\App\Models\ClientMedia::class,'media_bookmarks')
        ->where('bookmark_list_id',$listId)->withTimestamps();
    }




    public function ListNames($type)
    {
        return $this->hasMany(\App\Models\BookmarkList::class)
        ->where('type',$type);
    }


    public function Messages($sender = false)
    {
        if ($sender) return $this->belongsToMany(\App\Models\Message::class, 'message_bridges', 'sender_id')
            ->where('message_bridges.reciever_id', $sender)
            ->orWhere('message_bridges.sender_id', $sender)
            ->where('message_bridges.reciever_id', $this->id)->withTimestamps();

        return $this->belongsToMany(\App\Models\Message::class, 'message_bridges', 'sender_id')
            ->withTimestamps();
    }
    public function inboxList(){
        return self::whereNot('clients.id',$this->id)->select('name','clients.id')
        ->join('message_bridges',function($inbox){
            $inbox->on('clients.id','message_bridges.sender_id')
            ->orOn('clients.id','message_bridges.reciever_id');})->distinct();
    }


    public function BookmarkLists($type = false)
    {
        if ($type) return $this->hasMany(\App\Models\BookmarkList::class)->where('type', $type);
        return $this->hasMany(\App\Models\BookmarkList::class);
    }
}
