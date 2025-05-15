<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_user_status extends Model
{
    use HasFactory;
    protected $table = 'task_user_status';
    protected $fillable = ['task_id', 'user_id', 'user_status'];
}
