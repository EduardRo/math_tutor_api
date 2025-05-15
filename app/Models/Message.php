<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'prompt',
        'response',
        'source',
        'fallback_used',
    ];
}
