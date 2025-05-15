<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinnedMessage extends Model
{
    use HasFactory;
    protected $table = 'pinned_message';
    protected $fillable = ['chat_id', 'message_id', 'user_id', 'pinnedby_id'];
}
