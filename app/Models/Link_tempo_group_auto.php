<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link_tempo_group_auto extends Model
{
    use HasFactory;
    protected $table = "link_tempo_group_auto";
    protected $fillable = [
        'department_id',
        'user_id',
        'adder_id'
    ];

}
