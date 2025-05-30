<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function department_info()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
