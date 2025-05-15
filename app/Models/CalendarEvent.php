<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;
    protected $table = 'calendar_event';
    protected $fillable = ['user_id', 'department_id', 'title', 'type', 'start', 'end', 'color', 'border', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
