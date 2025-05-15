<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_group_member_auto extends Model
{
    use HasFactory;
    protected $table = "task_group_member_auto";

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Adjust foreign keys if needed
    }
}
