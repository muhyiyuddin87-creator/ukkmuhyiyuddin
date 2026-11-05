<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WakaParentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_can_access_data_orang_tua_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $response = $this->actingAs($waka)->get(route('waka.ortu'));
        $response->assertOk();
        $response->assertSee('Data Orang Tua');
        $response->assertSee('Daftar Akun Orang Tua');
    }

    public function test_waka_can_create_parent_account_linked_to_student(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $studentId = DB::table('siswas')->value('id');

        $response = $this->actingAs($waka)->post(route('waka.ortu.store'), [
            'fullname' => 'Bpk. Ahmad Subarjo',
            'username' => 'ortu.ahmad',
            'student_id' => $studentId,
            'phone' => '081234567890',
            'email' => 'ortu.ahmad@example.com',
            'password' => 'secret12345',
        ]);

        $response->assertRedirect(route('waka.ortu'));
        $response->assertSessionHas('success', 'Data akun orang tua berhasil ditambahkan.');

        $this->assertDatabaseHas('users', [
            'fullname' => 'Bpk. Ahmad Subarjo',
            'username' => 'ortu.ahmad',
            'student_id' => $studentId,
            'role_id' => 6,
            'is_active' => 1,
        ]);
    }

    public function test_waka_can_update_parent_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $students = DB::table('siswas')->take(2)->pluck('id');
        $studentId1 = $students[0];
        $studentId2 = $students[1] ?? $students[0];

        $parent = User::create([
            'fullname' => 'Ibu Siti Khodijah',
            'username' => 'ibu.siti',
            'student_id' => $studentId1,
            'password' => Hash::make('password123'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $response = $this->actingAs($waka)->patch(route('waka.ortu.update', $parent->id), [
            'fullname' => 'Ibu Siti Khodijah, M.Pd.',
            'username' => 'ibu.siti.update',
            'student_id' => $studentId2,
            'phone' => '08987654321',
            'email' => 'siti.update@example.com',
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('waka.ortu'));
        $response->assertSessionHas('success', 'Data akun orang tua berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'fullname' => 'Ibu Siti Khodijah, M.Pd.',
            'username' => 'ibu.siti.update',
            'student_id' => $studentId2,
            'phone' => '08987654321',
            'is_active' => 0,
        ]);
    }

    public function test_waka_can_delete_parent_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $studentId = DB::table('siswas')->value('id');

        $parent = User::create([
            'fullname' => 'Hapus Saya',
            'username' => 'hapus.ortu',
            'student_id' => $studentId,
            'password' => Hash::make('password123'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $response = $this->actingAs($waka)->delete(route('waka.ortu.destroy', $parent->id));
        $response->assertRedirect(route('waka.ortu'));
        $response->assertSessionHas('success', 'Akun orang tua berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $parent->id]);
    }

    public function test_waka_can_reset_parent_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $studentId = DB::table('siswas')->value('id');

        $parent = User::create([
            'fullname' => 'Bpk. Reset Sandi',
            'username' => 'reset.ortu',
            'student_id' => $studentId,
            'password' => Hash::make('oldpassword'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $response = $this->actingAs($waka)->post(route('waka.ortu.reset_password', $parent->id));
        $response->assertRedirect(route('waka.ortu'));
        $response->assertSessionHas('reset_info');

        $parent->refresh();
        $this->assertTrue(Hash::check('password123', $parent->password));
        $this->assertTrue((bool) $parent->must_change_password);
    }

    public function test_parent_can_login_and_redirects_to_student_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = DB::table('siswas')->first();

        User::create([
            'fullname' => 'Bpk. Joko',
            'username' => 'joko.ortu',
            'student_id' => $student->id,
            'password' => Hash::make('joko12345'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'joko.ortu',
            'password' => 'joko12345',
        ]);

        $response->assertRedirect(route('dashboard.role', ['role' => 'siswa']));
        $this->assertAuthenticated();

        // Mengakses dashboard siswa langsung
        $user = User::where('username', 'joko.ortu')->firstOrFail();
        $dashResponse = $this->actingAs($user)->get(route('dashboard.role', ['role' => 'siswa']));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Dashboard Orang Tua');
        $dashResponse->assertSee($student->name);
    }

    public function test_parent_can_access_student_jadwal_and_izin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = DB::table('siswas')->first();

        $parent = User::create([
            'fullname' => 'Bpk. Santoso',
            'username' => 'santoso.ortu',
            'student_id' => $student->id,
            'password' => Hash::make('santoso123'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $this->actingAs($parent)
            ->get(route('siswa.jadwal'))
            ->assertOk();

        $this->actingAs($parent)
            ->get(route('siswa.izin'))
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_parent_cannot_access_waka_or_guru_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = DB::table('siswas')->first();

        $parent = User::create([
            'fullname' => 'Bpk. Santoso',
            'username' => 'santoso.ortu',
            'student_id' => $student->id,
            'password' => Hash::make('santoso123'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $this->actingAs($parent)
            ->get('/dashboard/waka')
            ->assertForbidden();

        $this->actingAs($parent)
            ->get('/dashboard/guru')
            ->assertForbidden();
    }

    public function test_waka_can_search_accounts_in_kelola_akun_by_student_name_or_username(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $student = DB::table('siswas')->first();

        User::create([
            'fullname' => 'Wali Unik Subagyo',
            'username' => 'wali.subagyo',
            'student_id' => $student->id,
            'password' => Hash::make('password123'),
            'role_id' => 6,
            'is_active' => true,
        ]);

        $response = $this->actingAs($waka)
            ->get(route('waka.accounts', ['q' => 'Subagyo']))
            ->assertOk();

        $response->assertSee('Wali Unik Subagyo');
        $response->assertSee('wali.subagyo');
        $response->assertSee('Cari nama, username, siswa, kelas...');

        // Search by student name
        $responseStudent = $this->actingAs($waka)
            ->get(route('waka.accounts', ['q' => $student->name]))
            ->assertOk();

        $responseStudent->assertSee('Wali Unik Subagyo');
    }

    public function test_parent_and_student_can_share_the_same_student_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $studentUser = User::where('role_id', 3)->firstOrFail();
        $student = DB::table('siswas')->where('id', $studentUser->student_id)->first();

        // Create parent account linked to the EXACT same student that already has a student account
        $this->actingAs($waka)->post(route('waka.accounts.store'), [
            'fullname' => 'Orang Tua ' . $student->name,
            'username' => 'ortu.test.shared',
            'role_id' => 6,
            'student_id' => $student->id,
            'password' => 'password123',
        ])->assertRedirect(route('waka.accounts'));

        $this->assertDatabaseHas('users', [
            'username' => $studentUser->username,
            'role_id' => 3,
            'student_id' => $student->id,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'ortu.test.shared',
            'role_id' => 6,
            'student_id' => $student->id,
        ]);

        // Login as parent and see student dashboard with student name
        $parentUser = User::where('username', 'ortu.test.shared')->firstOrFail();
        $response = $this->actingAs($parentUser)->get(route('dashboard.role', ['role' => 'siswa']));
        $response->assertOk();
        $response->assertSee('Dashboard Orang Tua');
        $response->assertSee($student->name);
    }
}

