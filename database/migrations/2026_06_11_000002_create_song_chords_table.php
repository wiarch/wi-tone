<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_chords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->enum('instrument', ['guitar', 'keyboard']);
            $table->text('content');
            $table->timestamps();

            $table->unique(['song_id', 'instrument']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_chords');
    }
};
