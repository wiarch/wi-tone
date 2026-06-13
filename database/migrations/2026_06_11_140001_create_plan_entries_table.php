<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->string('type', 20);
            $table->string('section_title')->nullable();
            $table->foreignId('song_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('performance_key', 10)->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_plan_id', 'order']);
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->foreignId('director_contact_id')
                ->nullable()
                ->after('notes')
                ->constrained('contacts')
                ->nullOnDelete();
        });

        if (Schema::hasTable('plan_song')) {
            $rows = DB::table('plan_song')->orderBy('service_plan_id')->orderBy('order')->get();

            foreach ($rows as $row) {
                DB::table('plan_entries')->insert([
                    'service_plan_id' => $row->service_plan_id,
                    'order' => $row->order,
                    'type' => 'song',
                    'song_id' => $row->song_id,
                    'category_id' => $row->category_id ?? null,
                    'performance_key' => $row->performance_key ?? null,
                    'contact_id' => $row->contact_id ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('director_contact_id');
        });

        Schema::dropIfExists('plan_entries');
    }
};
