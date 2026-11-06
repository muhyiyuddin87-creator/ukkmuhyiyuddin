<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table) {
                if (! Schema::hasColumn('siswas', 'status')) {
                    $table->string('status', 20)->default('aktif')->after('email')->index();
                }
                if (! Schema::hasColumn('siswas', 'tahun_lulus')) {
                    $table->string('tahun_lulus', 20)->nullable()->after('status');
                }
                if (! Schema::hasColumn('siswas', 'tanggal_lulus')) {
                    $table->date('tanggal_lulus')->nullable()->after('tahun_lulus');
                }
            });
        }

        if (! Schema::hasTable('alumnis')) {
            Schema::create('alumnis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('nis', 50)->index();
                $table->string('name', 255)->index();
                $table->string('jurusan', 100)->nullable();
                $table->string('kelas_terakhir', 50)->nullable();
                $table->string('tahun_lulus', 20)->index();
                $table->date('tanggal_lulus');
                $table->text('alamat')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email', 100)->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('student_id')->references('id')->on('siswas')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnis');

        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn(['status', 'tahun_lulus', 'tanggal_lulus']);
            });
        }
    }
};
