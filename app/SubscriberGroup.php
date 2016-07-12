<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriberGroup extends Model
{
    protected $table = 'subscriber_groups';
    //
    protected $fillable = [
        'name'
    ];

    public function subscribers()
    {
        return $this->belongsToMany('App\Subscriber', 'subscribers_subscriber_groups', 'subscriber_group_id', 'subscriber_id');
    }

    public function setSubscribersIdsAttribute($value)
    {
        $this->subscribers()->detach();
        foreach($value as $subscriber_id)
            $this->subscribers()->attach($subscriber_id);
    }
    public function getSubscribersIdsAttribute()
    {
        $ids = [];
        foreach($this->subscribers as $subscriber)
            $ids[] = $subscriber->id;
        return $ids;
    }
    public function getSubscribersMailsAttribute()
    {
        $mails = [];
        foreach($this->subscribers as $subscriber)
            $mails[] = $subscriber->mail;
        return $mails;
    }

    public function sended_mails()
    {
        return $this->belongsToMany('App\SendedMail', 'mails_subscriber_groups', 'subscriber_group_id', 'sended_mail_id');
    }
}
