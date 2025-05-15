<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link_tempo_group extends Model
{
    use HasFactory;
    protected $table = 'link_tempo_group';
    protected $fillable = [
        'department_id',
        'user_id',
        'adder_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
