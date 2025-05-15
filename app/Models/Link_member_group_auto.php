<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link_member_group_auto extends Model
{
    use HasFactory;
    protected $table = "link_member_group_auto";
    protected $fillable = [
        'group_id',
        'template_id',
        'department_id',
        'link_id',
        'user_id',
        'adder_id'
    ];
}
