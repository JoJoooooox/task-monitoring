<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reactions extends Model
{
    use HasFactory;
    protected $table = 'reactions';
    protected $fillable = ['message_id', 'user_id', 'reaction'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
