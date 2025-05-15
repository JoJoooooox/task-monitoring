<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_solo_auto extends Model
{
    use HasFactory;
    protected $table = 'task_solo_auto';

    protected $fillable = [
        'template_id',
        'user_id',
        'adder_id',
        'type',
        'day',
        'time'
    ];
}
