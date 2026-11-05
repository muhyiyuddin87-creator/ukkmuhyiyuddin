<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_id')->nullable()->change();
            if (!Schema::hasColumn('attendances', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
            $table->unsignedBigInteger('journal_id')->nullable(false)->change();
        });
    }
};

