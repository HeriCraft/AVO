<?php

namespace App\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $table = 'educations';

    protected $fillable = [
        'candidate_id', 'institute', 'field', 'degree', 'from', 'to', 'description'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
