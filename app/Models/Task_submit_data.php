<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_submit_data extends Model
{
    use HasFactory;
    protected $table = 'table_task_submit_data';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
