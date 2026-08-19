<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_postings')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('NEW'); // NEW, PENDING_HUMAN_REVIEW, SHORTLISTED, HIRED, REJECTED
            $table->string('ai_score')->nullable(); // GREEN, YELLOW, RED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
