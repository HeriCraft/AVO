<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alter candidates table
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['job_post_id']);
            $table->dropColumn(['job_post_id', 'status', 'ai_score', 'name']);
            
            $table->string('tracking_id')->unique()->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->text('summary')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
        });

        // Create applications table
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('status')->default('NEW'); // NEW, PENDING_HUMAN_REVIEW, etc.
            $table->string('ai_score')->nullable(); // GREEN, YELLOW, RED
            $table->timestamps();
        });

        // Create experiences table
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('company');
            $table->string('location')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create educations table
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('institute');
            $table->string('field')->nullable();
            $table->string('degree')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Also we must update interviews to point to applications instead of candidates.
        // The prompt did not specify this, but to keep the dashboard working correctly with `application.jobPost`:
        // Actually, we can just keep interview linked to candidate.
        // The dashboard can query `Interview::with('candidate.applications.jobPost')`.
        // Let's not touch interviews to minimize risk unless absolutely necessary.
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('applications');
        
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('job_post_id')->nullable()->constrained('job_postings')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('status')->default('NEW');
            $table->string('ai_score')->nullable();
            
            $table->dropColumn([
                'tracking_id',
                'firstname',
                'lastname',
                'email',
                'summary',
                'address',
                'country'
            ]);
        });
    }
};
