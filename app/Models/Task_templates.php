<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_templates extends Model
{
    use HasFactory;
    protected $table = 'tasks_templates';

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
