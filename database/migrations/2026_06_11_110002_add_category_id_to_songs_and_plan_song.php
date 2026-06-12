<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('key')->constrained()->nullOnDelete();
        });

        Schema::table('plan_song', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('moment_type')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plan_song', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
