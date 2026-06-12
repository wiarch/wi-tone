<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_song', function (Blueprint $table) {
            $table->string('moment_type', 30)->nullable()->after('order');
            $table->string('performance_key', 10)->nullable()->after('moment_type');
            $table->foreignId('team_member_id')->nullable()->after('performance_key')->constrained('team_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plan_song', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_member_id');
            $table->dropColumn(['moment_type', 'performance_key']);
        });
    }
};
