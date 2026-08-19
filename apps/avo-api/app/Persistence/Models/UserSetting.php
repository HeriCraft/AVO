<?php

namespace App\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'ai_voice_tone',
        'ai_interview_language',
        'ai_strictness_level'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
