<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        Schema::create('user_school_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('school_class_id');
            $table->timestamps();
            $table->primary(['user_id', 'school_class_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('school_class_id')->references('id')->on('school_classes')->cascadeOnDelete();
        });

        $classNames = DB::table('siswas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->pluck('kelas');

        if (Schema::hasColumn('schedules', 'class_name')) {
            $classNames = $classNames->merge(
                DB::table('schedules')
                    ->whereNotNull('class_name')
                    ->where('class_name', '!=', '')
                    ->distinct()
                    ->pluck('class_name')
            );
        }

        foreach ($classNames->map(fn ($name) => trim((string) $name))->filter()->unique() as $name) {
            DB::table('school_classes')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_school_classes');
        Schema::dropIfExists('school_classes');
    }
};
