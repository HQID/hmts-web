<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "2026/2027"
            $table->unsignedSmallInteger('start_year')->default(2026);
            $table->unsignedSmallInteger('end_year')->default(2027);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->string('name'); // e.g. "Ristek", "Infokom"
            $table->string('full_name')->nullable(); // e.g. "Riset & Teknologi"
            $table->string('code', 20)->nullable(); // e.g. "DIVISI 01"
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('name');
            $table->string('nim', 30)->nullable();
            $table->string('position'); // e.g. "Ketua", "Ketua 1", "Ketua 2", "Sekretaris Umum", "Bendahara Umum"
            $table->string('batch', 10)->nullable(); // e.g. "'22", "'23"
            $table->string('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('periods');
    }
};
