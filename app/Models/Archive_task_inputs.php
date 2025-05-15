<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive_task_inputs extends Model
{
    use HasFactory;
    protected $table = 'archive_task_inputs';
    protected $fillable = ['task_id', 'field_id', 'value'];
}
