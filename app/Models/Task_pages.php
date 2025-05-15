<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_pages extends Model
{
    use HasFactory;
    protected  $table = 'task_pages';
    protected $fillable = [
        'task_id',
        'page_id',
        'page_content',
        'created_at',
        'updated_at'
    ];
}
