<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditMessage extends Model
{
    use HasFactory;
    protected $table = 'edit_message';
    protected $fillable = ['chat_id', 'message_id', 'user_id', 'message'];
}
