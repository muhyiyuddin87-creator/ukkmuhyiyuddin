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
        Schema::create('guru', function (Blueprint $table) {
            $table->id('id_guru');
            $table->string('nip', 30)->nullable()->unique();
            $table->string('nama_guru', 100);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('ruangan', function (Blueprint $table) {
            $table->id('id_ruangan');
            $table->string('nama_ruangan', 100);
            $table->string('jenis', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('jurusan', function (Blueprint $table) {
            $table->id('id_jurusan');
            $table->string('nama_jurusan', 100);
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id('id_kelas');
            $table->string('tingkat', 20)->nullable();
            $table->string('nama_kelas', 50);
            $table->unsignedBigInteger('id_jurusan')->nullable();
            $table->timestamps();

            $table->foreign('id_jurusan')->references('id_jurusan')->on('jurusan')->nullOnDelete();
        });

        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id('id_ortu');
            $table->string('nama_ortu', 100);
            $table->string('hubungan', 30)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id('id_siswa');
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nama_siswa', 100);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('agama', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('ortu_siswa', function (Blueprint $table) {
            $table->id('id_ortu_siswa');
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_ortu');
            $table->string('hubungan', 30)->nullable();
            $table->timestamps();

            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('id_ortu')->references('id_ortu')->on('orang_tua')->cascadeOnDelete();
        });

        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id('id_mapel');
            $table->string('nama_mapel', 100);
            $table->string('singkatan', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('tahun_ajar', function (Blueprint $table) {
            $table->id('id_tahun_ajar');
            $table->string('nama_tahun', 50);
            $table->enum('semester', ['Ganjil', 'Genap'])->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->unsignedBigInteger('id_guru');
            $table->unsignedBigInteger('id_mapel');
            $table->unsignedBigInteger('id_ruangan')->nullable();
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_tahun_ajar')->nullable();
            $table->string('hari', 20);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            $table->foreign('id_guru')->references('id_guru')->on('guru')->cascadeOnDelete();
            $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajaran')->cascadeOnDelete();
            $table->foreign('id_ruangan')->references('id_ruangan')->on('ruangan')->nullOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->cascadeOnDelete();
            $table->foreign('id_tahun_ajar')->references('id_tahun_ajar')->on('tahun_ajar')->nullOnDelete();
        });

        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->id('id_anggota');
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_kelas');
            $table->string('status', 30)->nullable();
            $table->timestamps();

            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->cascadeOnDelete();
        });

        Schema::create('plotting_ruangan_harian', function (Blueprint $table) {
            $table->id('id_plotting');
            $table->unsignedBigInteger('id_ruangan');
            $table->unsignedBigInteger('id_jadwal');
            $table->date('tanggal');
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_ruangan')->references('id_ruangan')->on('ruangan')->cascadeOnDelete();
            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_pelajaran')->cascadeOnDelete();
        });

        Schema::create('presensi_header', function (Blueprint $table) {
            $table->id('id_presensi');
            $table->unsignedBigInteger('id_jadwal');
            $table->unsignedBigInteger('id_ruangan')->nullable();
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreign('id_ruangan')->references('id_ruangan')->on('ruangan')->nullOnDelete();
        });

        Schema::create('presensi_detail', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_presensi');
            $table->enum('status_kehadiran', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Hadir');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('id_presensi')->references('id_presensi')->on('presensi_header')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_detail');
        Schema::dropIfExists('presensi_header');
        Schema::dropIfExists('plotting_ruangan_harian');
        Schema::dropIfExists('anggota_kelas');
        Schema::dropIfExists('jadwal_pelajaran');
        Schema::dropIfExists('tahun_ajar');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('ortu_siswa');
        Schema::dropIfExists('orang_tua');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('jurusan');
        Schema::dropIfExists('ruangan');
        Schema::dropIfExists('guru');
    }
};
