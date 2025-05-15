<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_tempo_group extends Model
{
    use HasFactory;
    protected $table = "task_tempo_group";

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
