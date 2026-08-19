<?php

namespace App\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];
    
    protected $casts = [
        'value' => 'json',
    ];
}
