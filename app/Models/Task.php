<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $table = 'tasks';
    protected $appends = ['assigned_user_names'];
    protected $fillable = [
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
        return $this->hasOne(Task_solo::class, 'task_id');
    }

    public function group()
    {
        return $this->hasMany(Task_group::class, 'task_id');
    }

    public function assignedUsers()
    {
        if ($this->type === 'Solo') {
            return $this->hasOne(Task_solo::class, 'task_id')->with('user');
        } elseif ($this->type === 'Group') {
            return $this->hasMany(Task_group::class, 'task_id')->with('user');
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

    public function task_solo()
    {
        return $this->hasMany(Task_solo::class, 'task_id');
    }

    // Relationship to Task_group
    public function task_group()
    {
        return $this->hasMany(Task_group::class, 'task_id');
    }
}
