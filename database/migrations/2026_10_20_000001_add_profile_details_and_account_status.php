<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable();
            $table->string('nip', 30)->nullable()->unique();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nis', 30)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropUnique(['nis']);
            $table->dropColumn('nis');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nip']);
            $table->dropColumn(['phone', 'nip', 'is_active']);
        });
    }
};
