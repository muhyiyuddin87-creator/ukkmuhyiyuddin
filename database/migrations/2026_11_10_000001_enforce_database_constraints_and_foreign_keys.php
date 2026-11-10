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
        // 1. Enable TiDB check constraint if using TiDB
        try {
            DB::statement('SET GLOBAL tidb_enable_check_constraint = ON');
        } catch (\Throwable $e) {
            // Ignore if not TiDB or insufficient permissions
        }

        // 2. TC-DBS-02: SQL Check Constraint Jam Mengajar (start_time < end_time)
        try {
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT chk_schedules_start_end CHECK (start_time < end_time)');
        } catch (\Throwable $e) {
            // Constraint may already exist
        }

        // 3. TC-DBS-01: Foreign Key RESTRICT on Delete
        // attendances.student_id -> siswas.id
        try {
            DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_student_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE attendances ADD CONSTRAINT attendances_student_id_foreign FOREIGN KEY (student_id) REFERENCES siswas (id) ON DELETE RESTRICT');
        } catch (\Throwable $e) {}

        // schedules.teacher_id -> users.id
        try {
            DB::statement('ALTER TABLE schedules DROP FOREIGN KEY schedules_teacher_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT schedules_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE RESTRICT');
        } catch (\Throwable $e) {}

        // journals.schedule_id -> schedules.id
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
