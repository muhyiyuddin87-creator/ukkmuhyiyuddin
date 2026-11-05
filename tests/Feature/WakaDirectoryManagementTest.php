<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AcademicYear;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WakaDirectoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_can_search_and_manage_student_records(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'waka')->firstOrFail());

        $studentPage = $this->get(route('waka.siswa'))->assertOk();
        foreach (['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'] as $jurusan) {
            $studentPage->assertSee('value="'.$jurusan.'"', false);
        }

        $this->post(route('waka.siswa.store'), [
            'name' => 'Siswa Dicari',
            'nis' => 'NIS-001',
            'phone' => '081234567890',
            'kelas' => 'XI RPL',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'alamat' => 'Bandung',
        ])->assertRedirect(route('waka.siswa'));

        $studentId = (int) DB::table('siswas')->where('name', 'Siswa Dicari')->value('id');
        $this->get(route('waka.siswa', ['q' => 'Dicari']))
            ->assertOk()
            ->assertSee('Siswa Dicari')
            ->assertSee('NIS-001')
            ->assertSee('081234567890');

        $this->patch(route('waka.siswa.update', $studentId), [
            'name' => 'Siswa Diperbarui',
            'nis' => 'NIS-002',
            'phone' => '081298765432',
            'kelas' => 'XI RPL',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'alamat' => 'Jakarta',
        ])->assertRedirect(route('waka.siswa'));

        $this->assertDatabaseHas('siswas', [
            'id' => $studentId,
            'name' => 'Siswa Diperbarui',
            'nis' => 'NIS-002',
            'phone' => '081298765432',
        ]);

        $this->delete(route('waka.siswa.destroy', $studentId))
            ->assertRedirect(route('waka.siswa'));
        $this->assertDatabaseMissing('siswas', ['id' => $studentId]);
    }

    public function test_waka_can_filter_students_by_major_and_grade(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'waka')->firstOrFail());

        DB::table('siswas')->insert([
            ['name' => 'Siswa XI RPL', 'kelas' => 'XI RPL 2', 'jurusan' => 'RPL', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siswa XII RPL', 'kelas' => 'XII RPL 2', 'jurusan' => 'RPL', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siswa XI TKJ', 'kelas' => 'XI TKJ 1', 'jurusan' => 'TKJ', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->get(route('waka.siswa', ['jurusan' => 'RPL', 'tingkat' => 'XI']))
            ->assertOk()
            ->assertSee('Siswa XI RPL')
            ->assertDontSee('Siswa XII RPL')
            ->assertDontSee('Siswa XI TKJ');
    }

    public function test_waka_can_filter_students_by_rombel_and_classes_have_rombel_one_to_four(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'waka')->firstOrFail());

        DB::table('siswas')->insert([
            ['name' => 'Siswa Rombel Satu', 'kelas' => 'X RPL 1', 'jurusan' => 'RPL', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siswa Rombel Dua', 'kelas' => 'X RPL 2', 'jurusan' => 'RPL', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siswa Rombel Tiga', 'kelas' => 'X RPL 3', 'jurusan' => 'RPL', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siswa Rombel Empat', 'kelas' => 'X RPL 4', 'jurusan' => 'RPL', 'alamat' => 'Alamat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $res = $this->get(route('waka.siswa'));
        $res->assertOk();
        $res->assertSee('Semua rombel');
        $res->assertSee('Rombel 1');
        $res->assertSee('Rombel 2');
        $res->assertSee('Rombel 3');
        $res->assertSee('Rombel 4');
        $res->assertSee('school-class-list');

        // Verifikasi school_classes memiliki rombel 1 sampai 4
        $this->assertDatabaseHas('school_classes', ['name' => 'X RPL 1']);
        $this->assertDatabaseHas('school_classes', ['name' => 'X RPL 2']);
        $this->assertDatabaseHas('school_classes', ['name' => 'X RPL 3']);
        $this->assertDatabaseHas('school_classes', ['name' => 'X RPL 4']);
        $this->assertDatabaseHas('school_classes', ['name' => 'XII LPS 4']);

        // Filter Rombel 3
        $this->get(route('waka.siswa', ['jurusan' => 'RPL', 'tingkat' => 'X', 'rombel' => '3']))
            ->assertOk()
            ->assertSee('Siswa Rombel Tiga')
            ->assertDontSee('Siswa Rombel Satu')
            ->assertDontSee('Siswa Rombel Dua')
            ->assertDontSee('Siswa Rombel Empat');

        // Filter Rombel 4
        $this->get(route('waka.siswa', ['jurusan' => 'RPL', 'tingkat' => 'X', 'rombel' => '4']))
            ->assertOk()
            ->assertSee('Siswa Rombel Empat')
            ->assertDontSee('Siswa Rombel Satu')
            ->assertDontSee('Siswa Rombel Dua')
            ->assertDontSee('Siswa Rombel Tiga');
    }

    public function test_waka_can_search_and_manage_teacher_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'waka')->firstOrFail());

        $this->post(route('waka.guru.store'), [
            'fullname' => 'Guru Dicari',
            'username' => 'guru.dicari',
            'password' => 'password123',
            'nip' => '198001012010011002',
            'phone' => '081234567890',
        ])->assertRedirect(route('waka.guru'));

        $teacher = User::where('username', 'guru.dicari')->firstOrFail();
        $this->get(route('waka.guru', ['q' => 'Dicari']))
            ->assertOk()
            ->assertSee('Guru Dicari')
            ->assertSee('198001012010011002')
            ->assertSee('081234567890');
        DB::table('schedules')->insert([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Bahasa Indonesia',
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->get(route('waka.guru'))
            ->assertOk()
            ->assertSee('Guru Dicari')
            ->assertSee('Bahasa Indonesia');

        $this->patch(route('waka.guru.update', $teacher->getKey()), [
            'fullname' => 'Guru Diperbarui',
            'username' => 'guru.diperbarui',
            'password' => 'password456',
            'nip' => '198001012010011003',
            'phone' => '081298765432',
        ])->assertRedirect(route('waka.guru'));

        $teacher->refresh();
        $this->assertSame('Guru Diperbarui', $teacher->fullname);
        $this->assertSame('198001012010011003', $teacher->nip);
        $this->assertSame('081298765432', $teacher->phone);
        $this->assertTrue(Hash::check('password456', $teacher->password));

        DB::table('schedules')->where('teacher_id', $teacher->getKey())->delete();
        $this->delete(route('waka.guru.destroy', $teacher->getKey()))
            ->assertRedirect(route('waka.guru'));
        $this->assertDatabaseMissing('users', ['id' => $teacher->getKey()]);
    }

    public function test_waka_can_assign_a_homeroom_class_from_the_teacher_list(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $this->actingAs($waka);

        $classId = DB::table('school_classes')->insertGetId([
            'name' => 'X RPL 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('waka.guru.store'), [
            'fullname' => 'Guru Wali',
            'username' => 'guru.wali',
            'password' => 'password123',
            'class_ids' => [$classId],
        ])->assertRedirect(route('waka.guru'));

        $teacher = User::where('username', 'guru.wali')->firstOrFail();
        $this->assertDatabaseHas('user_school_classes', [
            'user_id' => $teacher->getKey(),
            'school_class_id' => $classId,
        ]);

        $this->get(route('waka.guru'))
            ->assertOk()
            ->assertSee('Wali Kelas')
            ->assertSee('X RPL 1');

        $this->patch(route('waka.guru.update', $teacher->getKey()), [
            'fullname' => 'Guru Wali',
            'username' => 'guru.wali',
            'class_ids' => [],
        ])->assertRedirect(route('waka.guru'));

        $this->assertDatabaseMissing('user_school_classes', [
            'user_id' => $teacher->getKey(),
            'school_class_id' => $classId,
        ]);
    }

    public function test_waka_can_manage_rooms_and_assign_them_to_schedules(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $teacher = User::where('username', 'guru')->firstOrFail();
        $this->actingAs($waka);
        DB::table('siswas')->insert([
            [
                'name' => 'Siswa Kelas RPL',
                'kelas' => 'X RPL',
                'jurusan' => 'RPL',
                'alamat' => 'Alamat RPL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siswa Kelas TKJ',
                'kelas' => 'XI TKJ',
                'jurusan' => 'TKJ',
                'alamat' => 'Alamat TKJ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $this->get(route('waka.jadwal'))
            ->assertOk()
            ->assertSee('Tahun ajaran '.AcademicYear::current())
            ->assertSee('name="class_name"', false)
            ->assertSee('Jam ke-1')
            ->assertDontSee('>Sabtu</option>', false)
            ->assertDontSee('>Minggu</option>', false)
            ->assertSee('X RPL')
            ->assertSee('XI TKJ')
            ->assertDontSee('Kelas (ID)');

        $this->post(route('waka.ruangan.store'), [
            'nama_ruangan' => 'Lab Uji',
            'jenis' => 'Lab',
            'keterangan' => 'Ruang pengujian',
        ])->assertRedirect(route('waka.jadwal'));

        $roomId = (int) DB::table('ruangan')->where('nama_ruangan', 'Lab Uji')->value('id_ruangan');
        $this->post(route('waka.jadwal.store'), [
            'teacher_id' => $teacher->getKey(),
            'cycle_number' => 1,
            'day_of_week' => 'Senin',
            'period_code' => 'period_1',
            'subject' => 'Matematika',
            'room_id' => $roomId,
            'start_time' => '08:00',
            'end_time' => '08:35',
            'class_name' => 'X RPL',
            'cycle_type' => 'Regular',
        ])->assertRedirect(route('waka.jadwal'));

        $scheduleId = (int) DB::table('schedules')->where('subject', 'Matematika')->value('id');
        $this->assertDatabaseHas('schedules', [
            'id' => $scheduleId,
            'room_id' => $roomId,
            'academic_year' => AcademicYear::current(),
            'start_time' => '08:00',
            'end_time' => '08:35',
        ]);
        $currentStartYear = (int) substr(AcademicYear::current(), 0, 4);
        DB::table('schedules')->insert([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Jadwal Tahun Ajaran Lalu',
            'academic_year' => ($currentStartYear - 1).'/'.$currentStartYear,
            'day_of_week' => 'Selasa',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->get(route('waka.jadwal'))
            ->assertOk()
            ->assertDontSee('Jadwal Tahun Ajaran Lalu');

        $this->post(route('waka.jadwal.store'), [
            'teacher_id' => $teacher->getKey(),
            'cycle_number' => 1,
            'day_of_week' => 'Senin',
            'period_code' => 'period_1',
            'subject' => 'Fisika',
            'room_id' => $roomId,
            'start_time' => '08:30',
            'end_time' => '09:30',
            'class_name' => 'X RPL',
            'cycle_type' => 'Regular',
        ])->assertSessionHasErrors([
            'schedule_conflict' => 'Ruangan sudah digunakan pada waktu tersebut.',
        ]);

        $this->delete(route('waka.ruangan.destroy', $roomId))
            ->assertSessionHasErrors('room');

        $this->patch(route('waka.ruangan.update', $roomId), [
            'nama_ruangan' => 'Lab Diperbarui',
            'jenis' => 'Lab',
            'keterangan' => 'Keterangan baru',
        ])->assertRedirect(route('waka.jadwal'));

        $this->patch(route('waka.jadwal.update', $scheduleId), [
            'teacher_id' => $teacher->getKey(),
            'day_of_week' => 'Senin',
            'period_code' => 'period_1',
            'subject' => 'Matematika Diperbarui',
            'room_id' => $roomId,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'class_name' => 'XI TKJ',
            'cycle_type' => 'Regular',
        ])->assertRedirect(route('waka.jadwal'));

        $this->assertDatabaseHas('schedules', [
            'id' => $scheduleId,
            'subject' => 'Matematika Diperbarui',
            'class_name' => 'XI TKJ',
            'class_id' => null,
            'room_id' => $roomId,
        ]);

        $this->delete(route('waka.jadwal.destroy', $scheduleId))
            ->assertRedirect(route('waka.jadwal'));
        $this->delete(route('waka.ruangan.destroy', $roomId))
            ->assertRedirect(route('waka.jadwal'));

        $this->assertDatabaseMissing('schedules', ['id' => $scheduleId]);
        $this->assertDatabaseMissing('ruangan', ['id_ruangan' => $roomId]);
    }

    public function test_waka_schedules_use_day_specific_period_times_and_reject_weekends(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $teacher = User::where('username', 'guru')->firstOrFail();
        $this->actingAs($waka);

        foreach ([
            ['Senin', 'period_1', '08:00', '08:35'],
            ['Selasa', 'literasi', '07:00', '07:30'],
            ['Rabu', 'period_1', '07:15', '07:55'],
            ['Kamis', 'period_1', '07:15', '07:55'],
            ['Jumat', 'period_8', '13:15', '13:50'],
        ] as $index => [$day, $periodCode, $start, $end]) {
            $this->post(route('waka.jadwal.store'), [
                'teacher_id' => $teacher->getKey(),
                'cycle_number' => 1,
                'day_of_week' => $day,
                'period_code' => $periodCode,
                'subject' => 'Mapel '.$index,
                'start_time' => '07:00',
                'end_time' => '08:00',
                'cycle_type' => 'Regular',
            ])->assertRedirect(route('waka.jadwal'));

            $this->assertDatabaseHas('schedules', [
                'subject' => 'Mapel '.$index,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }

        $this->post(route('waka.jadwal.store'), [
            'teacher_id' => $teacher->getKey(),
            'cycle_number' => 1,
            'day_of_week' => 'Sabtu',
            'period_code' => 'period_1',
            'subject' => 'Jadwal Akhir Pekan',
            'start_time' => '07:00',
            'end_time' => '08:00',
            'cycle_type' => 'Regular',
        ])->assertSessionHasErrors('day_of_week');

        $this->assertDatabaseMissing('schedules', ['subject' => 'Jadwal Akhir Pekan']);
    }

    public function test_waka_cannot_edit_or_delete_a_schedule_in_a_locked_cycle(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $teacher = User::where('username', 'guru')->firstOrFail();
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($waka);
        $this->post(route('waka.jadwal.configure'), [
            'active_cycle' => 1,
            'locked' => 1,
        ])->assertRedirect(route('waka.jadwal'));

        $this->patch(route('waka.jadwal.update', $scheduleId), [
            'teacher_id' => $teacher->getKey(),
            'day_of_week' => 'Selasa',
            'period_code' => 'period_1',
            'subject' => 'Matematika',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
        ])->assertStatus(423);

        $this->delete(route('waka.jadwal.destroy', $scheduleId))
            ->assertStatus(423);
        $this->assertDatabaseHas('schedules', ['id' => $scheduleId]);
    }

    public function test_waka_can_delete_all_students_in_a_class(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $this->assertDatabaseHas('siswas', ['kelas' => 'X AKL']);
        $initialCount = DB::table('siswas')->where('kelas', 'X AKL')->count();
        $this->assertSame(10, $initialCount);

        $this->actingAs($waka)
            ->delete(route('waka.siswa.destroy_class'), [
                'kelas' => 'X AKL',
            ])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHas('success', 'Semua data siswa di kelas X AKL (10 siswa) berhasil dihapus.');

        $this->assertDatabaseMissing('siswas', ['kelas' => 'X AKL']);
    }

    public function test_waka_deleting_class_with_dependencies_requires_force_flag(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $student = DB::table('siswas')->where('kelas', 'XI BD')->first();
        DB::table('users')->where('username', 'siswa')->update(['student_id' => $student->id]);

        $this->actingAs($waka)
            ->delete(route('waka.siswa.destroy_class'), [
                'kelas' => 'XI BD',
            ])
            ->assertSessionHasErrors('kelas');

        $this->assertDatabaseHas('siswas', ['kelas' => 'XI BD']);

        $this->actingAs($waka)
            ->delete(route('waka.siswa.destroy_class'), [
                'kelas' => 'XI BD',
                'force' => 1,
            ])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('siswas', ['kelas' => 'XI BD']);
        $this->assertDatabaseMissing('users', ['student_id' => $student->id]);
    }

    public function test_non_waka_cannot_delete_students_by_class(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();

        $this->actingAs($teacher)
            ->delete(route('waka.siswa.destroy_class'), [
                'kelas' => 'X AKL',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('siswas', ['kelas' => 'X AKL']);
    }

    public function test_waka_can_delete_class_while_excluding_students_who_did_not_pass(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $students = DB::table('siswas')->where('kelas', 'X BD')->orderBy('id')->get();
        $this->assertCount(10, $students);

        $studentToKeep = $students->first();
        $studentsToDelete = $students->slice(1);

        $this->actingAs($waka)
            ->delete(route('waka.siswa.destroy_class'), [
                'kelas' => 'X BD',
                'exclude_ids' => [$studentToKeep->id],
            ])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHas('success', 'Sebanyak 9 data siswa di kelas X BD berhasil dihapus. (1 siswa yang tidak lulus/naik kelas dipertahankan).');

        // Excluded student is preserved
        $this->assertDatabaseHas('siswas', [
            'id' => $studentToKeep->id,
            'kelas' => 'X BD',
        ]);

        // Non-excluded students are deleted
        foreach ($studentsToDelete as $deleted) {
            $this->assertDatabaseMissing('siswas', [
                'id' => $deleted->id,
            ]);
        }
    }

    public function test_waka_cannot_delete_when_all_students_in_class_are_excluded(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $studentIds = DB::table('siswas')->where('kelas', 'X BD')->pluck('id')->all();

        $this->actingAs($waka)
            ->delete(route('waka.siswa.destroy_class'), [
                'kelas' => 'X BD',
                'exclude_ids' => $studentIds,
            ])
            ->assertSessionHasErrors('kelas');

        $this->assertSame(10, DB::table('siswas')->where('kelas', 'X BD')->count());
    }

    public function test_waka_can_filter_students_by_class(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $this->actingAs($waka)
            ->get(route('waka.siswa', ['kelas' => 'XI BD']))
            ->assertOk()
            ->assertSee('XI BD');
    }

    public function test_waka_can_fetch_class_students_as_json(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $response = $this->actingAs($waka)
            ->getJson(route('waka.siswa.class_students', ['kelas' => 'XI BD']))
            ->assertOk()
            ->assertJsonCount(10);

        $response->assertJsonStructure([
            '*' => ['id', 'name', 'nis'],
        ]);
    }

    public function test_waka_dashboard_renders_combined_clickable_cards(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $response = $this->actingAs($waka)
            ->get(route('dashboard.role', 'waka'))
            ->assertOk();

        // Verifikasi 4 kartu atas yang sudah disatukan menjadi link navigasi interaktif
        $response->assertSee(route('waka.siswa'), false);
        $response->assertSee(route('waka.guru'), false);
        $response->assertSee(route('waka.jadwal'), false);
        $response->assertSee(route('waka.laporan'), false);

        // Verifikasi teks kartu gabungan
        $response->assertSee('Data Siswa');
        $response->assertSee('Data Guru');
        $response->assertSee('Jadwal Mengajar');
        $response->assertSee('Laporan Kehadiran');

        // Verifikasi bagian dummy monitoring dan rapat/evaluasi/program sudah tidak ada
        $response->assertDontSee('Monitoring Sekolah');
        $response->assertDontSee('Agenda penting minggu ini');
        $response->assertDontSee('Jumlah laporan yang perlu ditinjau');
        $response->assertDontSee('Program sekolah aktif');
    }
}
