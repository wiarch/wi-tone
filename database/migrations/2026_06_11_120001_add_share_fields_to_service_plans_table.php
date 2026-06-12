<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_plans', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('notes');
            $table->timestamp('published_at')->nullable()->after('share_token');
            $table->json('share_settings')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropColumn(['share_token', 'published_at', 'share_settings']);
        });
    }
};
