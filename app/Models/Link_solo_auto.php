<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link_solo_auto extends Model
{
    use HasFactory;
    protected $table = "link_solo_auto";
    protected $fillable = [
        'user_id',
        'template_id',
        'link_id',
        'department_id',
        'adder_id',
        'type',
        'due',
        'start_time',
        'end_time',
        'start_day',
        'end_day',
    ];
}
