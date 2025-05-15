<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'log';

    // Optionally specify the primary key if it's not 'id'
    protected $primaryKey = 'id';

    // Optionally disable timestamps if your table doesn't have 'created_at' and 'updated_at'
    // public $timestamps = false;

    // Optionally specify which attributes are mass assignable
    protected $fillable = ['name', 'action', 'description'];

    protected $hidden = ['remember_token'];
}
