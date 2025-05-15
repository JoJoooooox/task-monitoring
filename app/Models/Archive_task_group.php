<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive_task_group extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'archive_task_group';
    protected $fillable = ['task_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
