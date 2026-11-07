<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_role_profiles_show_their_account_status_and_phone_field(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ([
            1 => 'waka',
            2 => 'guru',
            3 => 'siswa',
            4 => 'kesiswaan',
        ] as $roleId => $username) {
            $this->actingAs(User::where('role_id', $roleId)->firstOrFail())
                ->get(route('profile'))
                ->assertOk()
                ->assertSee('Status Akun:')
                ->assertSee('Aktif')
                ->assertSee('name="phone"', false);
        }
    }

    public function test_teacher_can_update_phone_and_nip_and_sees_scheduled_subjects(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();

        DB::table('schedules')->insert([
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

        $this->actingAs($teacher)
            ->post(route('profile.update'), [
                'fullname' => $teacher->fullname,
                'username' => $teacher->username,
                'phone' => '081234567890',
                'nip' => '198001012010011001',
                'email' => '',
            ])
            ->assertRedirect(route('profile'));

        $teacher->refresh();
        $this->assertSame('081234567890', $teacher->phone);
        $this->assertSame('198001012010011001', $teacher->nip);

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('198001012010011001');
    }

    public function test_linked_student_can_update_nis_and_phone(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('username', 'siswa')->firstOrFail();
        $studentId = DB::table('siswas')->insertGetId([
            'name' => $student->fullname,
            'kelas' => 'X-1',
            'jurusan' => 'IPA',
            'alamat' => 'Alamat siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student->student_id = $studentId;
        $student->save();

        $this->actingAs($student)
            ->post(route('profile.update'), [
                'fullname' => $student->fullname,
                'username' => $student->username,
                'phone' => '081298765432',
                'nis' => '12345678',
                'email' => '',
            ])
            ->assertRedirect(route('profile'));

        $this->assertDatabaseHas('users', [
            'id' => $student->getKey(),
            'phone' => '081298765432',
        ]);
        $this->assertDatabaseHas('siswas', [
            'id' => $studentId,
            'nis' => '12345678',
        ]);

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('12345678');
    }

    public function test_student_profile_does_not_render_editable_nis_or_role_and_preserves_nis_on_update(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('username', 'siswa')->firstOrFail();
        $studentId = DB::table('siswas')->insertGetId([
            'name' => $student->fullname,
            'nis' => '99887766',
            'kelas' => 'XII-RPL-2',
            'jurusan' => 'RPL',
            'alamat' => 'Alamat siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student->student_id = $studentId;
        $student->save();

        $response = $this->actingAs($student)->get(route('profile'));
        $response->assertOk()
            ->assertDontSee('name="nis"', false)
            ->assertDontSee('Role / Peran')
            ->assertSee('99887766');

        $this->post(route('profile.update'), [
            'fullname' => $student->fullname,
            'username' => $student->username,
            'phone' => '08999999999',
            'email' => 'siswa_baru@sekolah.sch.id',
        ])->assertRedirect(route('profile'));

        $this->assertDatabaseHas('siswas', [
            'id' => $studentId,
            'nis' => '99887766',
            'phone' => '08999999999',
            'email' => 'siswa_baru@sekolah.sch.id',
        ]);
    }

    public function test_api_profile_exposes_and_updates_teacher_profile_details(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        Sanctum::actingAs($teacher);

        $this->putJson('/api/profile', [
            'fullname' => $teacher->fullname,
            'username' => $teacher->username,
            'phone' => '081234567890',
            'nip' => '198001012010011001',
        ])->assertOk()
            ->assertJsonPath('data.phone', '081234567890')
            ->assertJsonPath('data.nip', '198001012010011001');

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.phone', '081234567890')
            ->assertJsonPath('data.nip', '198001012010011001');
    }

    public function test_inactive_accounts_cannot_log_in_or_access_protected_profiles(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        DB::table('users')->where('id', $teacher->getKey())->update(['is_active' => false]);
        $teacher->refresh();

        $this->post('/login', [
            'username' => 'guru',
            'password' => 'guru123',
        ])->assertSessionHasErrors('account');
        $this->assertGuest();

        $this->postJson('/api/login', [
            'username' => 'guru',
            'password' => 'guru123',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Akun tidak aktif. Hubungi administrator.');

        $this->actingAs($teacher)
            ->get(route('profile'))
            ->assertRedirect(route('login'));
        $this->assertGuest();

        Sanctum::actingAs($teacher);
        $this->getJson('/api/profile')->assertForbidden();
    }

    public function test_student_dashboard_only_shows_schedules_for_the_students_class_and_active_cycle(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('username', 'siswa')->firstOrFail();
        $studentId = DB::table('siswas')->insertGetId([
            'name' => $student->fullname,
            'kelas' => 'XI RPL',
            'jurusan' => 'RPL',
            'alamat' => 'Alamat siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student->student_id = $studentId;
        $student->save();

        $teacherId = User::where('username', 'guru')->value('id');
        DB::table('schedules')->insert([
            [
                'teacher_id' => $teacherId,
                'class_name' => 'XI RPL',
                'subject' => 'Matematika XI',
                'day_of_week' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'cycle_type' => 'Regular',
                'cycle_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $teacherId,
                'class_name' => 'XII RPL',
                'subject' => 'Matematika XII',
                'day_of_week' => 'Senin',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'cycle_type' => 'Regular',
                'cycle_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $teacherId,
                'class_name' => 'XI RPL',
                'subject' => 'Fisika XI',
                'day_of_week' => 'Senin',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'cycle_type' => 'Regular',
                'cycle_number' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($student)
            ->get(route('dashboard.role', ['role' => 'siswa']))
            ->assertOk()
            ->assertSee('Kelas XI RPL')
            ->assertSee('Matematika XI')
            ->assertDontSee('Matematika XII')
            ->assertDontSee('Fisika XI')
            ->assertSee('>1</p>', false);

        $this->get(route('siswa.jadwal'))
            ->assertOk()
            ->assertSee('Kelas XI RPL')
            ->assertSee('Matematika XI')
            ->assertDontSee('Matematika XII')
            ->assertDontSee('Fisika XI');
    }
}
