<?php

namespace App\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'company', 'location', 'contract_type', 'from', 'to', 'description'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
