<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to optimize query performance across core tables.
     */
    public function up(): void
    {
        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table) {
                if (Schema::hasColumn('siswas', 'kelas') && ! Schema::hasIndex('siswas', ['kelas'])) {
                    $table->index('kelas', 'siswas_kelas_idx');
                }
                if (Schema::hasColumn('siswas', 'jurusan') && ! Schema::hasIndex('siswas', ['jurusan'])) {
                    $table->index('jurusan', 'siswas_jurusan_idx');
                }
                if (Schema::hasColumn('siswas', 'name') && ! Schema::hasIndex('siswas', ['name'])) {
                    $table->index('name', 'siswas_name_idx');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'username') && ! Schema::hasIndex('users', ['username'])) {
                    $table->index('username', 'users_username_idx');
                }
                if (Schema::hasColumn('users', 'role_id') && ! Schema::hasIndex('users', ['role_id'])) {
                    $table->index('role_id', 'users_role_id_idx');
                }
                if (Schema::hasColumn('users', 'fullname') && ! Schema::hasIndex('users', ['fullname'])) {
                    $table->index('fullname', 'users_fullname_idx');
                }
            });
        }

        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                if (Schema::hasColumn('schedules', 'class_name') && ! Schema::hasIndex('schedules', ['class_name'])) {
                    $table->index('class_name', 'schedules_class_name_idx');
                }
                if (Schema::hasColumn('schedules', 'day_of_week') && ! Schema::hasIndex('schedules', ['day_of_week'])) {
                    $table->index('day_of_week', 'schedules_day_of_week_idx');
                }
                if (Schema::hasColumn('schedules', 'academic_year') && Schema::hasColumn('schedules', 'cycle_number') && ! Schema::hasIndex('schedules', ['academic_year', 'cycle_number'])) {
                    $table->index(['academic_year', 'cycle_number'], 'schedules_academic_cycle_idx');
                }
            });
        }

        if (Schema::hasTable('journals')) {
            Schema::table('journals', function (Blueprint $table) {
                if (Schema::hasColumn('journals', 'date') && ! Schema::hasIndex('journals', ['date'])) {
                    $table->index('date', 'journals_date_idx');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'date') && ! Schema::hasIndex('attendances', ['date'])) {
                    $table->index('date', 'attendances_date_idx');
                }
                if (Schema::hasColumn('attendances', 'status') && ! Schema::hasIndex('attendances', ['status'])) {
                    $table->index('status', 'attendances_status_idx');
                }
            });
        }

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'status_approval') && ! Schema::hasIndex('leave_requests', ['status_approval'])) {
                    $table->index('status_approval', 'leave_requests_status_approval_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table) {
                if (Schema::hasIndex('siswas', 'siswas_kelas_idx')) {
                    $table->dropIndex('siswas_kelas_idx');
                }
                if (Schema::hasIndex('siswas', 'siswas_jurusan_idx')) {
                    $table->dropIndex('siswas_jurusan_idx');
                }
                if (Schema::hasIndex('siswas', 'siswas_name_idx')) {
                    $table->dropIndex('siswas_name_idx');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasIndex('users', 'users_username_idx')) {
                    $table->dropIndex('users_username_idx');
                }
                if (Schema::hasIndex('users', 'users_role_id_idx')) {
                    $table->dropIndex('users_role_id_idx');
                }
                if (Schema::hasIndex('users', 'users_fullname_idx')) {
                    $table->dropIndex('users_fullname_idx');
                }
            });
        }

        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                if (Schema::hasIndex('schedules', 'schedules_class_name_idx')) {
                    $table->dropIndex('schedules_class_name_idx');
                }
                if (Schema::hasIndex('schedules', 'schedules_day_of_week_idx')) {
                    $table->dropIndex('schedules_day_of_week_idx');
                }
                if (Schema::hasIndex('schedules', 'schedules_academic_cycle_idx')) {
                    $table->dropIndex('schedules_academic_cycle_idx');
                }
            });
        }

        if (Schema::hasTable('journals')) {
            Schema::table('journals', function (Blueprint $table) {
                if (Schema::hasIndex('journals', 'journals_date_idx')) {
                    $table->dropIndex('journals_date_idx');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasIndex('attendances', 'attendances_date_idx')) {
                    $table->dropIndex('attendances_date_idx');
                }
                if (Schema::hasIndex('attendances', 'attendances_status_idx')) {
                    $table->dropIndex('attendances_status_idx');
                }
            });
        }

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasIndex('leave_requests', 'leave_requests_status_approval_idx')) {
                    $table->dropIndex('leave_requests_status_approval_idx');
                }
            });
        }
    }
};
