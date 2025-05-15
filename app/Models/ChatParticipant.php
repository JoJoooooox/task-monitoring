<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    use HasFactory;
    protected $table = 'chat_participants';
    protected $fillable = ['chat_id', 'user_id', 'is_here', 'nickname', 'is_muted', 'is_admin', 'is_creator'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
