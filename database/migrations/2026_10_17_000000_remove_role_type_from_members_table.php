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
        if (Schema::hasColumn('members', 'role_type')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('role_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('members', 'role_type')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('role_type', 30)->default('staff');
            });
        }
    }
};
