<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'description', 'avatar', 'created_by'];

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}