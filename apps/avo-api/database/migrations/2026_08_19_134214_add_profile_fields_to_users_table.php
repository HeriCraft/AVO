<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('first_name')->nullable()->after('username');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('company')->nullable()->after('last_name');
            $table->string('company_role')->nullable()->after('company');
        });

        // Backfill existing users
        DB::table('users')->whereNull('username')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'username' => Str::slug(explode('@', $user->email)[0]) . '-' . $user->id
            ]);
        });
        
        // Now make it strictly not nullable if we want, but it's fine to leave it nullable at DB level if app enforces it.
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'first_name', 'last_name', 'company', 'company_role']);
        });
    }
};
