<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_id', 'user_id', 'reply_to_id',
        'body', 'file_path', 'file_name', 'file_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function reactionsGrouped()
    {
        return $this->reactions()
                    ->selectRaw('emoji, count(*) as count, group_concat(user_id) as user_ids')
                    ->groupBy('emoji');
    }
}