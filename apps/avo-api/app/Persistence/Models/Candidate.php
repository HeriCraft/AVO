<?php

namespace App\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = [
        'job_post_id',
        'name',
        'status',
        'ai_score',
        'created_at', // For seeding purposes
        'updated_at'
    ];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }
}
