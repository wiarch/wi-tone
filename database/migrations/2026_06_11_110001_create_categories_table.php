<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        $now = now();

        foreach ([
            ['name' => 'Adoración', 'slug' => 'adoracion'],
            ['name' => 'Himnos Menores', 'slug' => 'himnos-menores'],
            ['name' => 'Himnos Mayores', 'slug' => 'himnos-mayores'],
        ] as $category) {
            DB::table('categories')->insert([
                'user_id' => null,
                'name' => $category['name'],
                'slug' => $category['slug'],
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
