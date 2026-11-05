<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WakaAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_can_create_update_and_delete_accounts_with_roles(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $this->actingAs($waka);

        $this->get(route('waka.accounts'))
            ->assertOk()
            ->assertSee('Kelola Akun & Role', false)
            ->assertSee('Status Akun')
            ->assertSee('data-edit-button', false)
            ->assertSee('data-save-button hidden', false)
            ->assertSee('autocomplete="new-password"', false)
            ->assertSee('autocomplete="off"', false);

        $this->post(route('waka.accounts.store'), [
            'fullname' => 'Guru Baru',
            'username' => 'guru.baru',
            'password' => 'password123',
            'role_id' => 2,
        ])->assertRedirect(route('waka.accounts'));

        $account = User::where('username', 'guru.baru')->firstOrFail();
        $this->assertSame(2, (int) $account->role_id);
        $this->assertTrue(Hash::check('password123', $account->password));

        $this->patch(route('waka.accounts.update', $account->getKey()), [
            'fullname' => 'Siswa Baru',
            'username' => 'siswa.baru',
            'role_id' => 3,
            'student_id' => $this->createStudent(),
            'password' => 'password456',
            'is_active' => '0',
        ])->assertRedirect(route('waka.accounts'));

        $account->refresh();
        $this->assertSame('Siswa Baru', $account->fullname);
        $this->assertSame(3, (int) $account->role_id);
        $this->assertFalse((bool) $account->is_active);
        $this->assertNotNull($account->student_id);
        $this->assertTrue(Hash::check('password456', $account->password));

        $this->delete(route('waka.accounts.destroy', $account->getKey()))
            ->assertRedirect(route('waka.accounts'));
        $this->assertDatabaseMissing('users', ['id' => $account->getKey()]);
    }

    public function test_waka_can_assign_a_database_student_to_exactly_one_student_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $student = User::where('username', 'siswa')->firstOrFail();
        $studentId = $this->createStudent();

        $this->actingAs($waka)
            ->get(route('waka.accounts'))
            ->assertOk()
            ->assertSee('Data Siswa')
            ->assertSee('Siswa Uji');

        $this->patch(route('waka.accounts.update', $student->getKey()), [
            'fullname' => $student->fullname,
            'username' => $student->username,
            'role_id' => 3,
            'student_id' => $studentId,
        ])->assertRedirect(route('waka.accounts'));

        $this->assertDatabaseHas('users', [
            'id' => $student->getKey(),
            'student_id' => $studentId,
        ]);

        $this->post(route('waka.accounts.store'), [
            'fullname' => 'Siswa Duplikat',
            'username' => 'siswa.duplikat',
            'password' => 'password123',
            'role_id' => 3,
            'student_id' => $studentId,
        ])->assertSessionHasErrors('student_id');

        $this->assertDatabaseMissing('users', ['username' => 'siswa.duplikat']);
    }

    public function test_waka_cannot_change_or_delete_own_account_or_delete_accounts_in_use(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();
        $guru = User::where('username', 'guru')->firstOrFail();
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $guru->getKey(),
            'subject' => 'Matematika',
            'day_of_week' => 'Rabu',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($waka);
        $this->patch(route('waka.accounts.update', $waka->getKey()), [
            'fullname' => 'Waka',
            'username' => 'waka',
            'role_id' => 2,
        ])->assertSessionHasErrors('role_id');

        $this->delete(route('waka.accounts.destroy', $waka->getKey()))
            ->assertSessionHasErrors('account');
        $this->delete(route('waka.accounts.destroy', $guru->getKey()))
            ->assertSessionHasErrors('account');
        $this->patch(route('waka.accounts.update', $guru->getKey()), [
            'fullname' => $guru->fullname,
            'username' => $guru->username,
            'role_id' => 3,
        ])->assertSessionHasErrors('role_id');

        $this->assertDatabaseHas('users', ['id' => $waka->getKey(), 'role_id' => 1]);
        $this->assertDatabaseHas('users', ['id' => $guru->getKey()]);
        $this->assertDatabaseHas('schedules', ['id' => $scheduleId, 'teacher_id' => $guru->getKey()]);
    }

    public function test_non_waka_roles_cannot_manage_user_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'guru')->firstOrFail());

        $this->get(route('waka.accounts'))->assertForbidden();
        $this->post(route('waka.accounts.store'), [
            'fullname' => 'Akun Terlarang',
            'username' => 'terlarang',
            'password' => 'password123',
            'role_id' => 2,
        ])->assertForbidden();
    }

    private function createStudent(): int
    {
        return DB::table('siswas')->insertGetId([
            'name' => 'Siswa Uji',
            'kelas' => 'X-1',
            'jurusan' => 'IPA',
            'alamat' => 'Alamat Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
