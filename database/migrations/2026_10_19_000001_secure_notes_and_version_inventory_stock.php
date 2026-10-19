<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_notes', fn (Blueprint $table) => $table->string('file_disk', 20)->default('public'));
        Schema::table('inventories', fn (Blueprint $table) => $table->unsignedTinyInteger('stock_version')->default(1));
    }

    public function down(): void
    {
        Schema::table('meeting_notes', fn (Blueprint $table) => $table->dropColumn('file_disk'));
        Schema::table('inventories', fn (Blueprint $table) => $table->dropColumn('stock_version'));
    }
};
