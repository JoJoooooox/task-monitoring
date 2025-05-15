<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_inputs extends Model
{
    use HasFactory;
    protected $table = 'task_inputs';
    protected $fillable = ['task_id', 'field_id', 'value'];
}
