<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // -------------------------------------------------------------------------
        // 1. Mengaktifkan fitur CHECK constraint pada database engine TiDB / MySQL
        // -------------------------------------------------------------------------
        try {
            DB::statement('SET GLOBAL tidb_enable_check_constraint = ON');
        } catch (\Throwable $e) {
            // Abaikan jika database engine bukan TiDB atau sudah aktif secara default
        }

        // -------------------------------------------------------------------------
        // 2. FITUR TC-DBS-02: SQL CHECK CONSTRAINT JAM MENGAJAR DI DATABASE ENGINE
        // Tujuan: Memastikan query SQL langsung (misal via phpMyAdmin/DBeaver)
        // yang memasukkan start_time >= end_time otomatis ditolak oleh database engine
        // dengan pelanggaran Check Constraint Violation (error code 3819).
        // -------------------------------------------------------------------------
        try {
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT chk_schedules_start_end CHECK (start_time < end_time)');
        } catch (\Throwable $e) {
            // Constraint sudah ada sebelumnya
        }

        // -------------------------------------------------------------------------
        // 3. FITUR TC-DBS-01: PENOLAKAN HAPUS RELASI DI ENGINE DATABASE (INNODB RESTRICT)
        // Tujuan: Mengganti relasi ON DELETE CASCADE menjadi ON DELETE RESTRICT.
        // Jika penguji menjalankan query SQL langsung: DELETE FROM siswas / DELETE FROM users,
        // database engine secara tegas menolak eksekusi (Foreign Key Constraint Violation 1451)
        // untuk menjaga integritas data catatan presensi dan riwayat jurnal mengajar.
        // -------------------------------------------------------------------------
        
        // Relasi Presensi Siswa: attendances.student_id -> siswas.id (RESTRICT)
        try {
            DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_student_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE attendances ADD CONSTRAINT attendances_student_id_foreign FOREIGN KEY (student_id) REFERENCES siswas (id) ON DELETE RESTRICT');
        } catch (\Throwable $e) {}

        // Relasi Jadwal Guru: schedules.teacher_id -> users.id (RESTRICT)
        try {
            DB::statement('ALTER TABLE schedules DROP FOREIGN KEY schedules_teacher_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT schedules_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE RESTRICT');
        } catch (\Throwable $e) {}

        // Relasi Jurnal Jadwal: journals.schedule_id -> schedules.id (RESTRICT)
        try {
            DB::statement('ALTER TABLE journals DROP FOREIGN KEY journals_schedule_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE journals ADD CONSTRAINT journals_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES schedules (id) ON DELETE RESTRICT');
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE schedules DROP CHECK chk_schedules_start_end');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_student_id_foreign');
            DB::statement('ALTER TABLE attendances ADD CONSTRAINT attendances_student_id_foreign FOREIGN KEY (student_id) REFERENCES siswas (id) ON DELETE CASCADE');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE schedules DROP FOREIGN KEY schedules_teacher_id_foreign');
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT schedules_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE CASCADE');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE journals DROP FOREIGN KEY journals_schedule_id_foreign');
            DB::statement('ALTER TABLE journals ADD CONSTRAINT journals_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES schedules (id) ON DELETE CASCADE');
        } catch (\Throwable $e) {}
    }
};
