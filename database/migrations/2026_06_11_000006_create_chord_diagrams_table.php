<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chord_diagrams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chord_id')->constrained()->cascadeOnDelete();
            $table->enum('instrument', ['guitar', 'keyboard']);
            $table->string('variant_name');
            $table->json('representation');
            $table->timestamps();

            $table->unique(['chord_id', 'instrument', 'variant_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chord_diagrams');
    }
};
