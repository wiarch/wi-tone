<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_song', function (Blueprint $table) {
            $table->foreignId('service_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->primary(['service_plan_id', 'song_id']);
            $table->index(['service_plan_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_song');
    }
};
