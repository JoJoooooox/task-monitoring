<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive_task_solo extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'archive_task_solo';
    protected $fillable = [
        'task_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
