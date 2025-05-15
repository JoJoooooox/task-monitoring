<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function members()
    {
        return $this->hasMany(Member::class, 'department_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(Task_templates::class, 'department_id');
    }
}
