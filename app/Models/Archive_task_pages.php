<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive_task_pages extends Model
{
    use HasFactory;
    protected  $table = 'archive_task_pages';
    protected $fillable = [
        'task_id',
        'page_id',
        'page_content',
        'created_at',
        'updated_at'
    ];
}
