<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive_task extends Model
{
    use HasFactory;
    protected $table = 'archive_task';
    protected $appends = ['assigned_user_names'];
    protected $fillable = [
        'task_id',
        'template_id',
        'department_id',
        'status',  // Add this line
        'approved_by',
        'title',
        'user_status',
        'type',
        'last_updated_at'
    ];

    public function solo()
    {
        return $this->hasOne(Archive_task_solo::class, 'task_id');
    }

    public function group()
    {
        return $this->hasMany(Archive_task_group::class, 'task_id');
    }

    public function assignedUsers()
    {
        if ($this->type === 'Solo') {
            return $this->hasOne(Archive_task_solo::class, 'task_id')->with('user');
        } elseif ($this->type === 'Group') {
            return $this->hasMany(Archive_task_group::class, 'task_id')->with('user');
        }

        return null;
    }

    public function getAssignedUserNamesAttribute()
    {
        if ($this->type === 'Solo') {
            return optional($this->solo)->user ? $this->solo->user->name : 'No user assigned';
        } elseif ($this->type === 'Group') {
            $userNames = $this->group->pluck('user.name')->filter()->toArray();
            return !empty($userNames) ? implode(', ', $userNames) : 'No users assigned';
        }

        return 'No user assigned';
    }
}
