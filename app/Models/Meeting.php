<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $table = 'meetings';
    protected $fillable = [
        'room_id',
        'room_url',
        'room_name',
        'description',
        'started_at',
        'ended_at',
        'user_id',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function generateRoomId()
    {
        return uniqid().bin2hex(random_bytes(4));
    }
}
