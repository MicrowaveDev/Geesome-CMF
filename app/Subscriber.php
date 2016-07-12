<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscribers';
    //
    protected $fillable = [
        'name',
        'mail',
        'provider',
        'user_agent'
    ];

    public function groups()
    {
        return $this->belongsToMany('App\SubscriberGroup', 'subscribers_subscriber_groups', 'subscriber_id', 'subscriber_group_id');
    }

    public function setGroupsIdsAttribute($value)
    {
        $this->groups()->detach();
        foreach($value as $group_id)
            $this->groups()->attach($group_id);
    }
    public function getGroupsIdsAttribute()
    {
        $ids = [];
        foreach($this->groups as $group)
            $ids[] = $group->id;
        return $ids;
    }
}
